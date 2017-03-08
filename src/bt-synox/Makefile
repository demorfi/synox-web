BUILDDIR := build
SOURCE   := INFO SynoDLMSearchSynox.php
PKGNAME  := synox.dlm
PKGFILES := $(addprefix ${BUILDDIR}/,${SOURCE})

.PHONY: all clean distribute host
all: ${PKGNAME}

${PKGNAME}: host
	(cd ${BUILDDIR} ; tar -czf ../${PKGNAME} *)

host: $(BUILDDIR)
	@echo -n "Please enter host (default: http://localhost:8080/):";\
	read host;\
	echo $$host;

$(BUILDDIR):
	mkdir -p ${BUILDDIR}
	cp ${SOURCE} ${BUILDDIR}

clean:
	rm -rf ${BUILDDIR}
	rm -f ${PKGNAME}
