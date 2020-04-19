#!/bin/sh

# If you want to run cypress in ci you'll need to
# ensure we resolve the application absolute domain name to nginx service
# IP=`getent hosts nginx|cut -d ' ' -f 1`
# echo ${IP} ${ABSOLUTE_URL_DOMAIN} >> /etc/hosts

# Run cypress
npx cypress verify
# If running locally in CI
# Wait for site to be up and ready and then run the tests
# dockerize -wait ${ABSOLUTE_URL_SCHEME}://${ABSOLUTE_URL_DOMAIN} -timeout 2m exec npx cypress run
# Else a classical run is enough
exec npx cypress run
