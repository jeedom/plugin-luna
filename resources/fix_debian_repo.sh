#!/bin/bash

# Debian 11 (Bullseye) has been removed from the regular Debian mirrors, which
# breaks apt update/install on Luna boxes (always Bullseye) for every plugin,
# not just Luna's own dependencies. sources.list needs to be repointed to the
# Debian archive before anything else on the box relies on apt. Discussed and
# validated on the community:
# https://community.jeedom.com/t/installation-des-dependances-de-plugins-sous-debian-11-luna/150878
if ! grep -qE 'archive\.debian\.org|snapshot\.debian\.org' /etc/apt/sources.list; then
	sudo cp /etc/apt/sources.list /etc/apt/sources.list.bak-$(date +%s)
	sudo sed -i -E \
		-e '/bullseye-security/ s#https?://(deb|security)\.debian\.org/debian-security#https://snapshot.debian.org/archive/debian-security/20260830T000000Z#' \
		-e '/bullseye/ s#https?://(deb|security)\.debian\.org/debian#https://archive.debian.org/debian#' \
		-e '/bullseye/{/\[.*\]/! s/^(deb(-src)?) /\1 [check-valid-until=no] /}' \
		/etc/apt/sources.list
fi
