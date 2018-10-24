# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure("2") do |config|
  # The most common configuration options are documented and commented below.
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://vagrantcloud.com/search.
  config.vm.box = "ubuntu/bionic64"

  config.vm.hostname = "victim"

  # Disable automatic box update checking. If you disable this, then
  # boxes will only be checked for updates when the user runs
  # `vagrant box outdated`. This is not recommended.
  # config.vm.box_check_update = false

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  # NOTE: This will enable public access to the opened port
  # config.vm.network "forwarded_port", guest: 80, host: 8080

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine and only allow access
  # via 127.0.0.1 to disable public access
  # config.vm.network "forwarded_port", guest: 80, host: 8080, host_ip: "127.0.0.1"

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  config.vm.network "private_network", ip: "192.168.33.10"

  # Create a public network, which generally matched to bridged network.
  # Bridged networks make the machine appear as another physical device on
  # your network.
  # config.vm.network "public_network"

  # Share an additional folder to the guest VM. The first argument is
  # the path on the host to the actual folder. The second argument is
  # the path on the guest to mount the folder. And the optional third
  # argument is a set of non-required options.
  config.vm.synced_folder "./", "/var/www/demo", type: "nfs"

  # Provider-specific configuration so you can fine-tune various
  # backing providers for Vagrant. These expose provider-specific options.
  # Example for VirtualBox:
  #
  config.vm.provider "virtualbox" do |vb|
  #   # Display the VirtualBox GUI when booting the machine
  #   vb.gui = true
  #
  #   # Customize the amount of memory on the VM:
    vb.memory = "2048"
  end
  #
  # View the documentation for the provider you are using for more
  # information on available options.

  # Enable provisioning with a shell script. Additional provisioners such as
  # Puppet, Chef, Ansible, Salt, and Docker are also available. Please see the
  # documentation for more information about their specific syntax and use.
  config.vm.provision "shell", inline: <<-SHELL
    add-apt-repository ppa:linuxuprising/java
    apt-get update
    echo "oracle-java10-installer shared/accepted-oracle-license-v1-1 select true" | sudo debconf-set-selections
    debconf-set-selections <<< "postfix postfix/mailname string demo-forum-php.com"
    debconf-set-selections <<< "postfix postfix/main_mailer_type string 'Internet Site'"

    apt-get install -y pkg-config libmagickwand-dev libmagickcore-dev apache2 php7.2-cli php7.2-curl php7.2-json php7.2-mysql oracle-java10-set-default php7.2-dom libapache2-mod-php7.2 mailutils php7.2-gd php7.2-dev imagemagick
    pecl install imagick

    ES_V=$(curl -XGET 'localhost:9200' 2> /dev/null | grep number | tr -d '[:space:]')
    if [ "$ES_V" != '"number":"6.4.2",' ] ;
    then
        echo "Installing elasticsearch"
        wget -q https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-6.4.2.deb
        dpkg -i elasticsearch-6.4.2.deb
        rm elasticsearch-6.4.2.deb
        sudo systemctl enable elasticsearch.service
    fi

    rm /etc/apache2/sites-enabled/demo-forum-php.conf /etc/apache2/conf-enabled/default-server-name.conf
    ln -s /vagrant/VBOX/demo-forum-php.conf /etc/apache2/sites-enabled/0000-demo-forum-php.conf
    ln -s /vagrant/VBOX/default-server-name.conf /etc/apache2/conf-enabled/default-server-name.conf
    a2dissite 000-default.conf
    apache2ctl graceful
  SHELL
end
