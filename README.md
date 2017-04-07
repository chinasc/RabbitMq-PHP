# RabbitMq-PHP
rabbitmq with php
# 使用的是RabbitMq
# 扩展配置windows下：
# 1、下载amqp扩展，请根据自己php版本选择 http://pecl.php.NET/package/amqp/1.4.0/windows 
# 2、将php_amqp.dll放在php的ext目录里，然后修改php.ini文件 extension=php_amqp.dll
# 3、将rabbitmq.1.dll文件放在php的根目录里(也就是ext目录的父级目录) LoadFile  "D:/xampp/php/rabbitmq.1.dll"
# 4、你需要配置一个q服务，关于配置，你google一下就行了
# 扩展配置网上一大堆，随便找的。
# 关于Q服务器配置，网上一搜一大堆，没写了
# 只写了一个client客户端。没写服务端，但服务端会增加queue，读取，阻塞读取（因为我项目中我只需要写就行了。。。）。


# 只要把上述4点配置完成了，你就能运行本demo了。