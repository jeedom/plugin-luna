#!/bin/bash

# Debian 11 (Bullseye) has been removed from the regular Debian mirrors, which
# breaks apt update/install on Luna boxes (always Bullseye) for every plugin.
sudo sed -i -E \
	-e '/bullseye-security/ s#https?://(deb|security)\.debian\.org/debian-security#https://snapshot.debian.org/archive/debian-security/20260830T000000Z#' \
	-e '/bullseye/ s#https?://(deb|security)\.debian\.org/debian#https://archive.debian.org/debian#' \
	-e '/bullseye/{/\[.*\]/! s/^(deb(-src)?) /\1 [check-valid-until=no] /}' \
	/etc/apt/sources.list
