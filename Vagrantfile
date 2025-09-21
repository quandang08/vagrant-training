# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/focal64"
  config.vm.box_version = "20240821.0.1"

  # Network: private + forward port
  config.vm.network "private_network", ip: "192.168.33.10"
  config.vm.network "forwarded_port", guest: 8080, host: 8080   # web-backend
  config.vm.network "forwarded_port", guest: 8081, host: 8081   # phpMyAdmin
  config.vm.network "forwarded_port", guest: 3306, host: 3306   # MySQL

  # Sync folder
  config.vm.synced_folder "./sources", "/vagrant"

  # VirtualBox config
  config.vm.provider "virtualbox" do |vb|
    vb.gui = false
    vb.memory = "4096"
    vb.cpus = 2
  end

  # Provisioning
  config.vm.provision "shell", inline: <<-SHELL
    apt-get update
    apt-get install -y docker.io docker-compose git net-tools make
    sudo usermod -aG docker vagrant

    # Chạy docker-compose khi VM khởi động
    cd /vagrant
    sudo docker-compose up -d --build
  SHELL

  config.vm.boot_timeout = 600
end
