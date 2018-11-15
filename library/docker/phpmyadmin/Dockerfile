# vim: set ft=dockerfile :
# Configuration file for Docker

FROM phpmyadmin/phpmyadmin

# Add custom overrides
COPY phpmyadmin/config.php /etc/phpmyadmin/config.user.inc.php

# RUN echo "\$cfg['Servers'][1]['only_db'] = \"${MYSQL_DATABASE}\";" \
#   >> /etc/phpmyadmin/config.user.inc.php

