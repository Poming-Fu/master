
#For company manage baord service  

#==================================#  
server : main => [10.148.175.12](http://10.148.175.12/web1/index.php) , develop => [10.148.174.70](http://10.148.174.70/web1/index.php)  

Backend : PHP, node.js  

database : mariaDB  (http://10.148.175.12/phpmyadmin/index.php)

Frontend : JavaScript, CSS, Bootstrap, HTML  

#==================================#  

部署新web server時記得mount NAS 
sudo vim /etc/fstab
//10.148.165.16/Golden_FW  /mnt/Golden_FW  cifs  credentials=/etc/samba/credentials,iocharset=utf8,nofail  0  0
//10.148.165.16/DB         /mnt/DB         cifs  credentials=/etc/samba/credentials,iocharset=utf8,nofail  0  0

#==================================#


#continue to develop release form now ~~~
