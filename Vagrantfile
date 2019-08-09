# -*- mode: ruby -*-
# vi: set ft=ruby :

$script = <<-SCRIPT
apt-get install -y curl git htop sudo
apt-get install -y php7.2-cli php7.2-xml php7.2-curl php7.2-zip php7.2-mbstring php7.2-bcmath
if [ ! -f /usr/local/bin/composer ]; then
    CURL=`which curl`; $CURL -sS https://getcomposer.org/installer > installer.php
    php installer.php --filename="composer" --install-dir="/usr/local/bin"
    rm installer.php
fi
sudo -u vagrant composer install -d /vagrant --no-dev
SCRIPT

Vagrant.configure("2") do |config|
   config.vm.box = "bento/ubuntu-18.04"
    if Vagrant.has_plugin?("vagrant-cachier")
        config.cache.scope = :box
    end
    config.vm.provision "shell", inline: $script
end
