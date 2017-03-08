BUILDDIR := build
SOURCE   := INFO SynoFileHostingSynox.php
PKGNAME  := synox.host
PKGFILES := $(addprefix ${BUILDDIR}/,${SOURCE})

.PHONY: all clean distribute
all: ${PKGNAME}

${PKGNAME}: $(BUILDDIR)
	(cd ${BUILDDIR} ; tar -czf ../${PKGNAME} *)

$(BUILDDIR):
	mkdir -p ${BUILDDIR}
	cp ${SOURCE} ${BUILDDIR}

clean:
	rm -rf ${BUILDDIR}
	rm -f ${PKGNAME}
