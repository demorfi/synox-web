BUILDDIR := builds
VERSION := 2.0

.PHONY: all clean distribute
all: synox.aum synox.dlm synox.host build

synox.aum: ${BUILDDIR}
	$(MAKE) -C src/au-synox && \
	cp src/au-synox/synox.aum ${BUILDDIR}

synox.dlm: ${BUILDDIR}
	$(MAKE) -C src/bt-synox && \
	cp src/bt-synox/synox.dlm ${BUILDDIR}

synox.host: ${BUILDDIR}
	$(MAKE) -C src/ht-synox && \
	cp src/ht-synox/synox.host ${BUILDDIR}

build: ${BUILDDIR}
	cp LICENSE ${BUILDDIR}
	cp README.md ${BUILDDIR}
	cp -R web ${BUILDDIR}
	(cd ${BUILDDIR} ; zip -r synox-${VERSION}.zip ./ ; mv synox-${VERSION}.zip ../)

$(BUILDDIR):
	mkdir -p ${BUILDDIR}

clean:
	rm -rf ${BUILDDIR}
	rm -f synox-${VERSION}.zip
	$(MAKE) clean -C src/au-synox
	$(MAKE) clean -C src/bt-synox
	$(MAKE) clean -C src/ht-synox
