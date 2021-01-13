FROM centos:latest

ARG SSH_KEY="ssh_key"

#parpare ENV
RUN dnf install make git -y \
    && dnf install 'dnf-command(config-manager)' -y \
    && dnf config-manager --set-enabled PowerTools \
    && dnf install https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm -y \
    && dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm -y \
    && dnf module enable php:remi-7.4 -y \
    && dnf install php php-cli php-common php-pear -y \
    && dnf install php-devel -y \
    && mkdir ~/.ssh \
    && ssh-keyscan bitbucket.org > ~/.ssh/known_hosts \
    && echo -e "$SSH_KEY" > ~/.ssh/id_rsa \
    && chmod 600 ~/.ssh/id_rsa \
    && git clone git@bitbucket.org:wangrunxin/swoole.git /var/www/html/swoole \
    && pear config-set php_ini /etc/php.ini \
    && echo -e 'extension=swoole.so' /etc/php.d/ext-swoole.ini \
    && pecl install swoole -y

# install app
RUN dnf -y install wget \ 
    && wget https://getcomposer.org/installer -O composer-installer.php \
    && php composer-installer.php --filename=composer --install-dir=/usr/local/bin \
    && echo "{}" > ~/.composer/composer.json \
    && cd /var/www/html/swoole && \php /usr/local/bin/composer install -vvv

# start app
RUN cd /var/www/html/swoole \
    && git pull \
    && cp App/Db/DBConfig-example.php App/Db/DBConfig.php \ 
    && php easyswoole start d
    
# prod only    
# RUN rm -rf /root/.ssh/ 