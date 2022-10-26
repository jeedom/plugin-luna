/* vi: set sw=4 ts=4: */
/*
 * Minimal i2c-tools implementation for busybox.
 * Parts of code ported from i2c-tools:
 * 		http://www.lm-sensors.org/wiki/I2CTools.
 *
 * Copyright (C) 2014 by Bartosz Golaszewski <bartekgola@gmail.com>
 *
 * Licensed under GPLv2 or later, see file LICENSE in this source tree.
 */
//config:config I2CGET
//config:	bool "i2cget (5.5 kb)"
//config:	default y
//config:	help
//config:	Read from I2C/SMBus chip registers.
//config:
//config:config I2CSET
//config:	bool "i2cset (6.7 kb)"
//config:	default y
//config:	help
//config:	Set I2C registers.
//config:
//config:config I2CDUMP
//config:	bool "i2cdump (7.1 kb)"
//config:	default y
//config:	help
//config:	Examine I2C registers.
//config:
//config:config I2CDETECT
//config:	bool "i2cdetect (7.1 kb)"
//config:	default y
//config:	help
//config:	Detect I2C chips.
//config:
//config:config I2CTRANSFER
//config:	bool "i2ctransfer (4.0 kb)"
//config:	default y
//config:	help
//config:	Send user-defined I2C messages in one transfer.
//config:

//applet:IF_I2CGET(APPLET(i2cget, BB_DIR_USR_SBIN, BB_SUID_DROP))
//applet:IF_I2CSET(APPLET(i2cset, BB_DIR_USR_SBIN, BB_SUID_DROP))
//applet:IF_I2CDUMP(APPLET(i2cdump, BB_DIR_USR_SBIN, BB_SUID_DROP))
//applet:IF_I2CDETECT(APPLET(i2cdetect, BB_DIR_USR_SBIN, BB_SUID_DROP))
//applet:IF_I2CTRANSFER(APPLET(i2ctransfer, BB_DIR_USR_SBIN, BB_SUID_DROP))
/* not NOEXEC: if hw operation stalls, use less memory in "hung" process */

//kbuild:lib-$(CONFIG_I2CGET) += i2c_tools.o
//kbuild:lib-$(CONFIG_I2CSET) += i2c_tools.o
//kbuild:lib-$(CONFIG_I2CDUMP) += i2c_tools.o
//kbuild:lib-$(CONFIG_I2CDETECT) += i2c_tools.o
//kbuild:lib-$(CONFIG_I2CTRANSFER) += i2c_tools.o

/*
 * Unsupported stuff:
 *
 * - upstream i2c-tools can also look-up i2c busses by name, we only accept
 *   numbers,
 * - bank and bankreg parameters for i2cdump are not supported because of
 *   their limited usefulness (see i2cdump manual entry for more info),
 * - i2cdetect doesn't look for bus info in /proc as it does in upstream, but
 *   it shouldn't be a problem in modern kernels.
 */

#include <errno.h>
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
#include <linux/i2c.h>

// pwj
#include "common.h"
// add end


#define I2CDUMP_NUM_REGS		256

#define I2CDETECT_MODE_AUTO		0
#define I2CDETECT_MODE_QUICK		1
#define I2CDETECT_MODE_READ		2

/* linux/i2c-dev.h from i2c-tools overwrites the one from linux uapi
 * and defines symbols already defined by linux/i2c.h.
 * Also, it defines a bunch of static inlines which we would rather NOT
 * inline. What a mess.
 * We need only these definitions from linux/i2c-dev.h:
 */
#define I2C_SLAVE			0x0703
#define I2C_SLAVE_FORCE			0x0706
#define I2C_FUNCS			0x0705
#define I2C_PEC				0x0708
#define I2C_SMBUS			0x0720
#define I2C_RDWR			0x0707
#define I2C_RDWR_IOCTL_MAX_MSGS		42
#define I2C_RDWR_IOCTL_MAX_MSGS_STR	"42"
struct i2c_smbus_ioctl_data {
	__u8 read_write;
	__u8 command;
	__u32 size;
	union i2c_smbus_data *data;
};
struct i2c_rdwr_ioctl_data {
	struct i2c_msg *msgs;	/* pointers to i2c_msgs */
	__u32 nmsgs;		/* number of i2c_msgs */
};

/* end linux/i2c-dev.h */

// pwj
#define ENABLE_I2CSET 1

typedef unsigned char uint8_t;
typedef unsigned short uint16_t;

#define isblank(a) ({ unsigned char bb__isblank = (a); bb__isblank == ' ' || bb__isblank == '\t'; })

void bb_simple_error_msg_and_die(char *msg)
{
	printf("%s\n", msg);
	exit(-1);
}

void bb_simple_error_msg(char *msg)
{
	printf("%s\n", msg);
}

int bb_ask_y_confirmation_FILE(FILE *fp)
{
	char first = 0;
	int c;

	fflush(NULL);
	while (((c = fgetc(fp)) != EOF) && (c != '\n')) {
		if (first == 0 && !isblank(c)) {
			first = c|0x20;
		}
	}

	return first == 'y';
}

int bb_ask_y_confirmation(void)
{
	return bb_ask_y_confirmation_FILE(stdin);
}

// Die if we can't open a file and return a fd.
int xopen3(const char *pathname, int flags, int mode)
{
	int ret;

	ret = open(pathname, flags, mode);
	if (ret < 0) {
		printf("can't open '%s'", pathname);
		exit(-1);
	}
	return ret;
}

// Die if we can't open a file and return a fd.
int xopen(const char *pathname, int flags)
{
	return xopen3(pathname, flags, 0666);
}

int ioctl_or_perror_and_die(int fd, unsigned request, void *argp, const char *fmt,...)
{
	int ret;
	va_list p;

	ret = ioctl(fd, request, argp);
	if (ret < 0) {
		printf("ioctl err %s\n", strerror(errno));
		exit(-1);
	}
	return ret;
}
// pwj add end


/*
 * This is needed for ioctl_or_perror_and_die() since it only accepts pointers.
 */
static inline void *itoptr(int i)
{
	return (void*)(intptr_t)i;
}

static int32_t i2c_smbus_access(int fd, char read_write, __u8 cmd,
				int size, union i2c_smbus_data *data)
{
	struct i2c_smbus_ioctl_data args;

	args.read_write = read_write;
	args.command = cmd;
	args.size = size;
	args.data = data;

	return ioctl(fd, I2C_SMBUS, &args);
}

static int32_t i2c_smbus_read_byte(int fd)
{
	union i2c_smbus_data data;
	int err;

	err = i2c_smbus_access(fd, I2C_SMBUS_READ, 0, I2C_SMBUS_BYTE, &data);
	if (err < 0)
		return err;

	return data.byte;
}

#if ENABLE_I2CGET || ENABLE_I2CSET || ENABLE_I2CDUMP
static int32_t i2c_smbus_write_byte(int fd, uint8_t val)
{
	return i2c_smbus_access(fd, I2C_SMBUS_WRITE,
				val, I2C_SMBUS_BYTE, NULL);
}

static int32_t i2c_smbus_read_byte_data(int fd, uint8_t cmd)
{
	union i2c_smbus_data data;
	int err;

	err = i2c_smbus_access(fd, I2C_SMBUS_READ, cmd,
			       I2C_SMBUS_BYTE_DATA, &data);
	if (err < 0)
		return err;

	return data.byte;
}

static int32_t i2c_smbus_read_word_data(int fd, uint8_t cmd)
{
	union i2c_smbus_data data;
	int err;

	err = i2c_smbus_access(fd, I2C_SMBUS_READ, cmd,
			       I2C_SMBUS_WORD_DATA, &data);
	if (err < 0)
		return err;

	return data.word;
}
#endif /* ENABLE_I2CGET || ENABLE_I2CSET || ENABLE_I2CDUMP */

#if ENABLE_I2CSET
static int32_t i2c_smbus_write_byte_data(int file,
					 uint8_t cmd, uint8_t value)
{
	union i2c_smbus_data data;

	data.byte = value;

	return i2c_smbus_access(file, I2C_SMBUS_WRITE, cmd,
				I2C_SMBUS_BYTE_DATA, &data);
}

static int32_t i2c_smbus_write_word_data(int file, uint8_t cmd, uint16_t value)
{
	union i2c_smbus_data data;

	data.word = value;

	return i2c_smbus_access(file, I2C_SMBUS_WRITE, cmd,
				I2C_SMBUS_WORD_DATA, &data);
}

static int32_t i2c_smbus_write_block_data(int file, uint8_t cmd,
				   uint8_t length, const uint8_t *values)
{
	union i2c_smbus_data data;

	if (length > I2C_SMBUS_BLOCK_MAX)
		length = I2C_SMBUS_BLOCK_MAX;

	memcpy(data.block+1, values, length);
	data.block[0] = length;

	return i2c_smbus_access(file, I2C_SMBUS_WRITE, cmd,
				I2C_SMBUS_BLOCK_DATA, &data);
}

static int32_t i2c_smbus_write_i2c_block_data(int file, uint8_t cmd,
				       uint8_t length, const uint8_t *values)
{
	union i2c_smbus_data data;

	if (length > I2C_SMBUS_BLOCK_MAX)
		length = I2C_SMBUS_BLOCK_MAX;

	memcpy(data.block+1, values, length);
	data.block[0] = length;

	return i2c_smbus_access(file, I2C_SMBUS_WRITE, cmd,
				I2C_SMBUS_I2C_BLOCK_BROKEN, &data);
}
#endif /* ENABLE_I2CSET */

#if ENABLE_I2CDUMP
/*
 * Returns the number of bytes read, vals must hold at
 * least I2C_SMBUS_BLOCK_MAX bytes.
 */
static int32_t i2c_smbus_read_block_data(int fd, uint8_t cmd, uint8_t *vals)
{
	union i2c_smbus_data data;
	int i, err;

	err = i2c_smbus_access(fd, I2C_SMBUS_READ, cmd,
			       I2C_SMBUS_BLOCK_DATA, &data);
	if (err < 0)
		return err;

	for (i = 1; i <= data.block[0]; i++)
		*vals++ = data.block[i];
	return data.block[0];
}

static int32_t i2c_smbus_read_i2c_block_data(int fd, uint8_t cmd,
					     uint8_t len, uint8_t *vals)
{
	union i2c_smbus_data data;
	int i, err;

	if (len > I2C_SMBUS_BLOCK_MAX)
		len = I2C_SMBUS_BLOCK_MAX;
	data.block[0] = len;

	err = i2c_smbus_access(fd, I2C_SMBUS_READ, cmd,
			       len == 32 ? I2C_SMBUS_I2C_BLOCK_BROKEN :
					   I2C_SMBUS_I2C_BLOCK_DATA, &data);
	if (err < 0)
		return err;

	for (i = 1; i <= data.block[0]; i++)
		*vals++ = data.block[i];
	return data.block[0];
}
#endif /* ENABLE_I2CDUMP */

#if ENABLE_I2CDETECT
static int32_t i2c_smbus_write_quick(int fd, uint8_t val)
{
	return i2c_smbus_access(fd, val, 0, I2C_SMBUS_QUICK, NULL);
}
#endif /* ENABLE_I2CDETECT */

static void i2c_set_pec(int fd, int pec)
{
	ioctl_or_perror_and_die(fd, I2C_PEC,
				itoptr(pec ? 1 : 0),
				"can't set PEC");
}

static void i2c_set_slave_addr(int fd, int addr, int force)
{
	ioctl_or_perror_and_die(fd, force ? I2C_SLAVE_FORCE : I2C_SLAVE,
				itoptr(addr),
				"can't set address to 0x%02x", addr);
}


/*
 * Opens the device file associated with given i2c bus.
 *
 * Upstream i2c-tools also support opening devices by i2c bus name
 * but we drop it here for size reduction.
 */
static int i2c_dev_open(int i2cbus)
{
	char filename[sizeof("/dev/i2c-%d") + sizeof(int)*3];
	int fd;

	sprintf(filename, "/dev/i2c-%d", i2cbus);
	fd = open(filename, O_RDWR);
	if (fd < 0) {
		if (errno == ENOENT) {
			filename[8] = '/'; /* change to "/dev/i2c/%d" */
			fd = xopen(filename, O_RDWR);
		} else {
			printf("can't open '%s'", filename);
			exit(-1);
		}
	}

	return fd;
}

/* Size reducing helpers for xxx_check_funcs(). */
static void get_funcs_matrix(int fd, unsigned long *funcs)
{
	ioctl_or_perror_and_die(fd, I2C_FUNCS, funcs,
			"can't get adapter functionality matrix");
}

#if ENABLE_I2CGET || ENABLE_I2CSET || ENABLE_I2CDUMP
static void check_funcs_test_end(int funcs, int pec, const char *err)
{
	if (pec && !(funcs & (I2C_FUNC_SMBUS_PEC | I2C_FUNC_I2C)))
		printf("warning: adapter does not support PEC");

	if (err) {
		printf("adapter has no %s capability", err);
		exit(-1);
	}
}
#endif /* ENABLE_I2CGET || ENABLE_I2CSET || ENABLE_I2CDUMP */

/*
 * The below functions emit an error message and exit if the adapter doesn't
 * support desired functionalities.
 */
#if ENABLE_I2CGET || ENABLE_I2CDUMP
static void check_read_funcs(int fd, int mode, int data_addr, int pec)
{
	unsigned long funcs;
	const char *err = NULL;

	get_funcs_matrix(fd, &funcs);
	switch (mode) {
	case I2C_SMBUS_BYTE:
		if (!(funcs & I2C_FUNC_SMBUS_READ_BYTE)) {
			err = "SMBus receive byte";
			break;
		}
		if (data_addr >= 0 && !(funcs & I2C_FUNC_SMBUS_WRITE_BYTE))
			err = "SMBus send byte";
		break;
	case I2C_SMBUS_BYTE_DATA:
		if (!(funcs & I2C_FUNC_SMBUS_READ_BYTE_DATA))
			err = "SMBus read byte";
		break;
	case I2C_SMBUS_WORD_DATA:
		if (!(funcs & I2C_FUNC_SMBUS_READ_WORD_DATA))
			err = "SMBus read word";
		break;
#if ENABLE_I2CDUMP
	case I2C_SMBUS_BLOCK_DATA:
		if (!(funcs & I2C_FUNC_SMBUS_READ_BLOCK_DATA))
			err = "SMBus block read";
		break;

	case I2C_SMBUS_I2C_BLOCK_DATA:
		if (!(funcs & I2C_FUNC_SMBUS_READ_I2C_BLOCK))
			err = "I2C block read";
		break;
#endif /* ENABLE_I2CDUMP */
	default:
		bb_simple_error_msg_and_die("internal error");
	}
	check_funcs_test_end(funcs, pec, err);
}
#endif /* ENABLE_I2CGET || ENABLE_I2CDUMP */

#if ENABLE_I2CSET
static void check_write_funcs(int fd, int mode, int pec)
{
	unsigned long funcs;
	const char *err = NULL;

	get_funcs_matrix(fd, &funcs);
	switch (mode) {
	case I2C_SMBUS_BYTE:
		if (!(funcs & I2C_FUNC_SMBUS_WRITE_BYTE))
			err = "SMBus send byte";
		break;

	case I2C_SMBUS_BYTE_DATA:
		if (!(funcs & I2C_FUNC_SMBUS_WRITE_BYTE_DATA))
			err = "SMBus write byte";
		break;

	case I2C_SMBUS_WORD_DATA:
		if (!(funcs & I2C_FUNC_SMBUS_WRITE_WORD_DATA))
			err = "SMBus write word";
		break;

	case I2C_SMBUS_BLOCK_DATA:
		if (!(funcs & I2C_FUNC_SMBUS_WRITE_BLOCK_DATA))
			err = "SMBus block write";
		break;
	case I2C_SMBUS_I2C_BLOCK_DATA:
		if (!(funcs & I2C_FUNC_SMBUS_WRITE_I2C_BLOCK))
			err = "I2C block write";
		break;
	}
	check_funcs_test_end(funcs, pec, err);
}
#endif /* ENABLE_I2CSET */

static void confirm_or_abort(void)
{
	fprintf(stderr, "Continue? [y/N] ");
	if (!bb_ask_y_confirmation())
		bb_simple_error_msg_and_die("aborting");
}

/*
 * Return only if user confirms the action, abort otherwise.
 *
 * The messages displayed here are much less elaborate than their i2c-tools
 * counterparts - this is done for size reduction.
 */
static void confirm_action(int bus_addr, int mode, int data_addr, int pec)
{
	bb_simple_error_msg("WARNING! This program can confuse your I2C bus");

	/* Don't let the user break his/her EEPROMs */
	if (bus_addr >= 0x50 && bus_addr <= 0x57 && pec) {
		bb_simple_error_msg_and_die("this is I2C not smbus - using PEC on I2C "
			"devices may result in data loss, aborting");
	}

	if (mode == I2C_SMBUS_BYTE && data_addr >= 0 && pec)
		bb_simple_error_msg("WARNING! May interpret a write byte command "
			"with PEC as a write byte data command");

	if (pec)
		bb_simple_error_msg("PEC checking enabled");

	confirm_or_abort();
}

static void wirte_led(char *tmp_led, int fd, int val)
{
	int i;
	int data_addr;
	int status;

	for (i=0; i<6; i++) {
		data_addr = tmp_led[i];

		status = i2c_smbus_write_byte_data(fd, data_addr, val);
		if (status < 0) {
			bb_simple_error_msg_and_die("Write i2c failed");
		}
	}

}

#define BRIGHTNESS  0x8
#define MAX_BRIGHTNESS  0x1f  // max is 0x33
int i2cset_main(int color, int mode, int mode_param)
{
	char red_led[6] =   {4, 7, 10, 11, 17, 14};
	char blue_led[6] =  {3, 6, 9, 12, 18, 15};
	char green_led[6] = {2, 5, 8, 13, 19, 16};
	int bus_num, bus_addr, data_addr, i2cmode, pec = 0;
	int val, fd, status;
	int i;
	char *tmp_led;

	bus_num = 0;
	bus_addr = 0x23;

	i2cmode = I2C_SMBUS_BYTE_DATA; /* Implicit b */

	fd = i2c_dev_open(bus_num);
	check_write_funcs(fd, i2cmode, pec);
	i2c_set_slave_addr(fd, bus_addr, 1);

	// enable led power
	data_addr = 0x0;
	val = 0x0;
	status = i2c_smbus_write_byte_data(fd, data_addr, val);
	if (status < 0) {
		bb_simple_error_msg_and_die("Write i2c failed");
	}

	// enable all leds	
	data_addr = 0x1;
	val = 0x3f;
	status = i2c_smbus_write_byte_data(fd, data_addr, val);
	if (status < 0) {
		bb_simple_error_msg_and_die("Write i2c failed");
	}

	if (color == COLOR_RED) {
		wirte_led(blue_led, fd, 0);
		wirte_led(green_led, fd, 0);
		tmp_led = red_led;
	} else if (color == COLOR_BLUE) {
		wirte_led(red_led, fd, 0);
		wirte_led(green_led, fd, 0);
		tmp_led = blue_led;
	} else if (color == COLOR_GREEN) {
		wirte_led(blue_led, fd, 0);
		wirte_led(red_led, fd, 0);
		tmp_led = green_led;
	}

	if (mode == MODE_SIMPLE) {
		if (mode_param == MODE_SIMPLE_ON)
			val = BRIGHTNESS;
		else
			val = 0x0;

		wirte_led(tmp_led, fd, val);
		return 0;
	}

	if (mode == MODE_BLINK) {
		int sleep_time;

		if (mode_param == MODE_BLINK_SLOW)
			sleep_time = 1000000;
		else
			sleep_time = 300000;

		val = 0;
		while (1) {
			/* toggle on/off */
			if (val == 0)
				val = BRIGHTNESS;
			else
				val = 0;

			wirte_led(tmp_led, fd, val);
			usleep(sleep_time);
		}
	}


	if (mode == MODE_BREATHE) {
		while (1) {
			for (val = 2; val<MAX_BRIGHTNESS; val++) {
				wirte_led(tmp_led, fd, val);
				usleep(150000);
			}
			//usleep(100000);
			for (val = MAX_BRIGHTNESS-2; val>=1; val--) {
				wirte_led(tmp_led, fd, val);
				usleep(100000);
			}
		}
	}

	return 0;

}


