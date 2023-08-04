


## Author Email : yayv.cn(a)gmail.com

## 项目说明:
	本项目为一个 php 开发框架。主要思想来源于 Ruby On Rails 的核心思想: 规范大于配置

## 目录说明:
    cake => 核心程序的目录，包括2个子目录 develop，kernel
    cake/develop => 开发期使用的基础类和相关的工具程序
    cake/kernel  => 发布运行时，需要用这个目录内的基础类和相关工具程序，以保证更高的执行效率
    install => 本目录为tinycake框架的demo项目，同时，她也承担了自动化部署，更新配置等工具性的职责
	install/m => module， 数据模型对应的类放在此目录下
	install/v => views    展现层放在此目录下，基本是 html js css images 文件
	install/c => controllers, 负责处理url参数，并调用

## 流程说明:
	系统接收到一个请求后，首先用rewrite方式交给 index.php进行处理
	index.php 负责按url分析，并判断具体对应该url处理的类是否存在
	找到并加载正确的url处理类(controller)之后，调用controller的标准入口函数

## 使用的注意事项:
    所有需要调用配置文件的地方，都用 $config 或 $CONFIG 读取变量，这样才能让autoconfig工具生效
    所有的配置项里的url和path, 末尾都不要带 '/' 所有需要前面带目录或url的 目录或文件，都以 '/' 开头，保证一致性，就可以简化维护成本

## Smarty模板系统报错
    Fatal error: Uncaught --> Smarty Compiler: Syntax error in template "/Data/webapps/minisites/pyapp.com/templates/index.templates" on line 12 "<title>{$title->title showname='首页标题' }</title>" unexpected "showname" attribute <-- thrown in /Users/liuce/Projects/tinycake/cake/libraries/smarty3/sysplugins/smarty_internal_templatecompilerbase.php on line 12

    针对 Smarty3:
    需要修改文件 tinycake/cake/libraries/smarty3/sysplugins/smarty_internal_compilebase.php 第113行。整行注释掉。这样就可以在smarty模板的{}符号内随意增加自己需要的属性了。
	
    针对Smarty2:
    需要修改2处，估计现在使用smarty2模板的人已经非常少了，如果有人需要可以留言，我再把修改方法放出来。
    
smarty 应该有controller负责判断是否需要加载，临时目录放到哪里。

模板中，可以使用 {$baseurl} 标示当前系统所在的根url, 末尾不带 '/'
{$themepath} ，可以表示当前主题的相对目录，以'/'开头,末尾不带'/'


## 其他:
###    功能结构:
	1. 一级功能: 新建项目,并生成基础代码
	2. 导入项目, 把已有项目在本地建立,便于在本地进行代码分析
	3. 项目列表,进入项目列表
	4. 代码升级, 给框架提供了一个代码升级的接口,同时也可以直接升级现有羡慕代码的index.php

###    二级项目功能:
	1. todo列表
	2. 控制器, 动作; 模型,方法列表
	3. 日志管理及分析
	4. 项目配置文件生成
	5. 压力测试
	6. 日志分析的url合并规则的管理

## 其他
    项目列表,写入文本文件,因为不会有太多,上百就了不得了
