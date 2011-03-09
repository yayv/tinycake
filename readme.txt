
作者: 刘策

email: yayv.cn@gmail.com

项目说明:
	本项目为一个 php 开发框架。主要思想来源于 Ruby On Rails 的核心思想: 规范大于配置

目录说明:
    cake => 核心程序的目录，包括2个子目录 develop，kernel
    cake/develop => 开发期使用的基础类和相关的工具程序
    cake/kernel  => 发布运行时，需要用这个目录内的基础类和相关工具程序，以保证更高的执行效率
    install => 本目录为tinycake框架的demo项目，同时，她也承担了自动化部署，更新配置等工具性的职责
	install/m => module， 数据模型对应的类放在此目录下
	install/v => views    展现层放在此目录下，基本是 html js css images 文件
	install/c => controllers, 负责处理url参数，并调用

流程说明:
	系统接收到一个请求后，首先用rewrite方式交给 index.php进行处理
	index.php 负责按url分析，并判断具体对应该url处理的类是否存在
	找到并加载正确的url处理类(controller)之后，调用controller的标准入口函数

使用的注意事项:
    所有需要调用配置文件的地方，都用 $config 或 $CONFIG 读取变量，这样才能让autoconfig工具生效
    所有的配置项里的url和path, 末尾都不要带 '/' 所有需要前面带目录或url的 目录或文件，都以 '/' 开头，保证一致性，就可以简化维护成本

	
smarty 应该有controller负责判断是否需要加载，临时目录放到哪里。

模板中，可以使用 {$baseurl} 标示当前系统所在的根url, 末尾不带 '/'
{$themepath} ，可以表示当前主题的相对目录，以'/'开头,末尾不带'/'

