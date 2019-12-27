if [ ! -f docker/php/config/xdebug.ini ]; then
    read -p "enter Xdebug ide key:[docker]: " IDEKEY
    IDEKEY=${IDEKEY:-docker}
    cp ../config/xdebug.ini.tpl docker/php/config/xdebug.ini
    printf "\nxdebug.remote_host=$DOCKER_HOST_IP" >> docker/php/config/xdebug.ini
    printf "\nxdebug.idekey=$IDEKEY" >> docker/php/config/xdebug.ini
fi
