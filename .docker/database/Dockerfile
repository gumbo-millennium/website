# vim: set ft=dockerfile :
# Configuration file for Docker database

# Based on MySQL 8
FROM mysql:8

# Add data SQL files
COPY data/*.sql /docker-entrypoint-initdb.d/
