all: dsled

# if you want quick debug, "make CC=aarch64-buildroot-linux-gnu-gcc"

dsled: dsled.o i2c_tools.o
	$(CC) -o $@ $^

clean:
	rm -rf *.o
	rm -rf dsled

install:
#	$(INSTALL) -D -m 0755 dsled $(TOPDIR)/../debian/aa/usr/bin
	$(INSTALL) -D -m 0755 dsled $(TARGET_DIR)/usr/bin

