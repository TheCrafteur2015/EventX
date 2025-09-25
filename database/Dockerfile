FROM mysql

WORKDIR /

COPY create.sql /create.sql
COPY init.sh /init.sh

EXPOSE 3306

RUN chmod 777 init.sh

CMD [ "./init.sh" ]