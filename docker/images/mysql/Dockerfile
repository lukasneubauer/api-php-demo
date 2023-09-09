FROM mysql:8.0

COPY docker/images/mysql/mysql.cnf /etc/mysql/conf.d/mysql.cnf

WORKDIR /

EXPOSE 3306 33060

CMD ["mysqld"]
