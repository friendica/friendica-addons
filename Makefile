# Build addons packages
SRC	=	buglink fortunate nsfw sniper uhremotestorage \
		calc impressum oembed statusnet widgets \
		communityhome js_upload piwik tictac wppost \
		convert ldapauth  poormancron tumblr \
		facebook membersince randplace twitter

DESTS = $(addsuffix .tgz,$(SRC))

all: $(DESTS)

%.tgz: %
	@echo -n Creating $@... 
	@tar czf $@ $<
	@echo " Done."