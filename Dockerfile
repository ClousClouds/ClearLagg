# Gunakan image PocketMine-MP yang sudah ada
FROM pmmp/pocketmine-mp:latest

# Salin source code dan plugin.yml ke dalam container
COPY src /home/pocketmine/plugins/ClearLagg/src
COPY plugin.yml /home/pocketmine/plugins/ClearLagg/

# Set workdir ke direktori PocketMine
WORKDIR /home/pocketmine

# Install necessary dependencies (e.g., PHP if it's missing)
RUN apk add --no-cache bash

# Build plugin menjadi .phar
RUN ./bin/php7/bin/php PocketMine-MP.phar --makeplugin ClearLagg \
    && mv ClearLagg.phar /home/pocketmine/ClearLagg.phar

# Jalankan PocketMine-MP
CMD ["./bin/php7/bin/php", "PocketMine-MP.phar"]
