# mysql-reader
a simple mysql reader used in production env


php-扩展 
==============

* ldap

程序入口 
==============

* public/index.php

sql检测策略
==============

* 只允许show |select |set开头的sql语
* 其他语句只是匹配一些危险操作给予提示，和执行逻辑无关，匹配不上的提示未知语句
* 自动根据“;”拆分sql语句
* 自动拆分带有子查询的语句，子查询和主查询都必须有limit设置
* 检测offset,limit,结果集行数，结果集大小

配置文件
==============

* config.php
* cp .config-example.php config.php
* mkdir logs && chmod 777 logs

future
==============

* sql检测策略持续优化
* ldap登陆支持切换ou分组

