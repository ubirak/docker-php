CURRENT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))
APP_IMAGE_NAME=ubirak/docker-php
TAGS_TO_PULL?=latest dev

.PHONY: pull
pull: $(TAGS_TO_PULL)

.PHONY: $(TAGS_TO_PULL)
$(TAGS_TO_PULL):
	docker image pull $(APP_IMAGE_NAME):$@ || echo "can't pull image $(APP_IMAGE_NAME):$@"

.PHONY: push
push:
	@docker image push $(APP_IMAGE_NAME)

.PHONY: build
build:
	@docker build \
		--cache-from $(APP_IMAGE_NAME):latest \
		--target latest \
		--tag $(APP_IMAGE_NAME):latest .
	@docker build \
		--cache-from $(APP_IMAGE_NAME):latest \
		--cache-from $(APP_IMAGE_NAME):dev \
		--target dev \
		--tag $(APP_IMAGE_NAME):dev .

.PHONY: qa
qa: cs static

.PHONY: test
test: unit

.PHONY: cs static unit
cs static unit:
	$(call run,$@,--env APP_ENV=test)

.PHONY: shell
shell:
	@$(call run,ash,--entrypoint= --tty)

define run
docker run \
	--rm \
	--interactive \
	--volume $(CURRENT_DIR):/home/php/app \
	--volume /var/run/docker.sock:/var/run/docker.sock \
	$(2) \
	$(APP_IMAGE_NAME):dev $(1)
endef
