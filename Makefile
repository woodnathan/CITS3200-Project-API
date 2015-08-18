
SOURCEDIR = .
SOURCES := $(shell find $(SOURCEDIR) -name '*.php')

.PHONY: lint

lint:
	$(foreach var,$(SOURCES),php -l $(var);)
