# MySQL 数据库

## 简介

炸毛框架的数据库组件对接了 MySQL 连接池，在使用过程中无需配置即可实现 MySQL 查询，同时拥有高并发。

目前 2.5 版本后炸毛框架底层采用了 `doctrine/dbal` 组件，可以方便地构建 SQL 语句。

> 文档正在加急编写！！！用的话可以先用 `MySQLManager::getWrapper()` 获取 wrapper 后返回的方法。

