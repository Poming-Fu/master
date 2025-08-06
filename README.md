
#For company manage baord service  

#==================================#  
server : main => [10.148.175.12](http://10.148.175.12/web1/index.php) , develop => [10.148.174.70](http://10.148.174.70/web1/index.php)  

Backend : PHP, node.js  

database : mariaDB  (http://10.148.175.12/phpmyadmin/index.php)

Frontend : JavaScript, CSS, Bootstrap, HTML  

#==================================#  
1. local mount
部署新web server時記得mount NAS 
sudo mount -t cifs //10.148.165.16/DB /mnt/DB -o username=sam,password=sam,iocharset=utf8
sudo mount -t cifs //10.148.165.16/Golden_FW /mnt/Golden_FW -o username=sam,password=sam,iocharset=utf8

2. setup reboot auto mount
sudo vim /etc/fstab
//10.148.165.16/DB /mnt/DB cifs username=sam,password=sam,iocharset=utf8 0 0
//10.148.165.16/Golden_FW /mnt/Golden_FW cifs username=sam,password=sam,iocharset=utf8 0 0

#==================================#


#continue to develop release form now ~~~
