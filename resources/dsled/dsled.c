#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <stdarg.h>
#include <signal.h>
#include <syslog.h>
#include <fcntl.h>
#include <unistd.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <sys/resource.h>
#include <sys/ioctl.h>

#include <errno.h>
#include <netdb.h>
#include <signal.h>
#include <limits.h>
#include <dirent.h>
#include <arpa/inet.h>
#include <sys/socket.h>
#include <net/if.h>

#include "common.h"

#define MAXLINE  1024

static void err_quit(const char *fmt, ...)
{
	va_list args;
	char buf[MAXLINE];

	va_start(args, fmt);
	vsnprintf(buf, MAXLINE-1, fmt, args);
	va_end(args);
	//syslog(LOG_DEBUG, buf);
	exit(-1);
}

static void daemonize(const char *cmd)
{
	int i, fd0, fd1, fd2;
	pid_t pid;
	struct rlimit rl;
	struct sigaction sa;

	/* * Clear file creation mask. */
	umask(0);
	
	/* * Get maximum number of file descriptors. */
	if (getrlimit(RLIMIT_NOFILE, &rl) < 0)
		err_quit("%s: can't get file limit\n", cmd);
	
	/* * Become a session leader to lose controlling TTY. */
	if ((pid = fork()) < 0)
		err_quit("%s: can't fork\n", cmd);
	else if (pid != 0) /* parent */
		exit(0);
	setsid();

	/* * Ensure future opens won't allocate controlling TTYs. */
	sa.sa_handler = SIG_IGN;
	sigemptyset(&sa.sa_mask);
	sa.sa_flags = 0;
	if (sigaction(SIGHUP, &sa, NULL) < 0)
		err_quit("%s: can't ignore SIGHUP\n", cmd);
	if ((pid = fork()) < 0)
		err_quit("%s: can't fork\n", cmd);
	else if (pid != 0) /* parent */
		exit(0);
	
	/* * Change the current working directory to the root so * we won't prevent file systems from being unmounted. */
	if (chdir("/") < 0)
		err_quit("%s: can't change directory to /\n", cmd);
	
	/* * Close all open file descriptors. */
	if (rl.rlim_max == RLIM_INFINITY)
		rl.rlim_max = 1024;
	for (i = 0; i < rl.rlim_max; i++)
		close(i);
	
	/* * Attach file descriptors 0, 1, and 2 to /dev/null. */
	fd0 = open("/dev/null", O_RDWR);
	fd1 = dup(0);
	fd2 = dup(0);
	
	if (fd0 != 0 || fd1 != 1 || fd2 != 2) {
		//syslog(LOG_ERR, "unexpected file descriptors %d %d %d",fd0, fd1, fd2);
		exit(1);
	}
}

void Usage(char *cmd_name)
{
	printf("Usage: %s [r|g|b] [on/off]\n", cmd_name);
	printf("       %s [r|g|b] [blink_slow|blink_fast]\n", cmd_name);
	printf("       %s [r|g|b] breathe\n\n", cmd_name);
	exit(0);
}

extern int i2cset_main();
int main(int argc, char * argv[])
{
	int ret;
	int color;
	int mode;
	int mode_param;

	if (argc != 3) {
		Usage(argv[0]);
	}

	if (strcmp(argv[1], "r") == 0) {
		color =  COLOR_RED;
	} else if (strcmp(argv[1], "b") == 0) {
		color =  COLOR_BLUE;
	} else if (strcmp(argv[1], "g") == 0) {
		color =  COLOR_GREEN;
	} else {
		Usage(argv[0]);
	}

	if (strcmp(argv[2], "on") == 0) {
		mode = MODE_SIMPLE;
		mode_param = MODE_SIMPLE_ON;
	} else if (strcmp(argv[2], "off") == 0) {
		mode = MODE_SIMPLE;
		mode_param = MODE_SIMPLE_OFF;
	} else if (strcmp(argv[2], "blink_slow") == 0) {
		mode = MODE_BLINK;
		mode_param = MODE_BLINK_SLOW;
	} else if (strcmp(argv[2], "blink_fast") == 0) {
		mode = MODE_BLINK;
		mode_param = MODE_BLINK_FAST;
	} else if (strcmp(argv[2], "breathe") == 0) {
		mode = MODE_BREATHE;
		mode_param = 0;  // don't care
	} else {
		Usage(argv[0]);
	}

	if (mode == MODE_BLINK || mode == MODE_BREATHE)
		daemonize(argv[0]);

	i2cset_main(color, mode, mode_param);
	return 0;
}

