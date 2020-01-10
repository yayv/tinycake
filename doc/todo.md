	有框架提供的系列工具:
TODO:
	.htaccess 文件管理
	acl 的支持如何做呢？ 
	git支持
	jwt、Timeout、RealIP 等众多中间件，随用随取。
	SVN支持
	为模型的基类实现一个调试用的输出接口
	TODO获取，并呈现的方法, 增加TODO管理功能
	为日志列表增加合并2个/多个日志文件的功能
	为日志增加删除 txt日志和删除php日志的功能
	为模型添加一个可以做测试的简单界面,为以后的单元测试和自动化测试做准备
	为模型的基类增加一个用于调试的输出接口
	允许用户用自己的类库替换框架的标准库（如 smarty 换成其他版本或其他模板引擎，
	内置框架升级功能. 使用git还是svn?
	增加代码建模工具，用于将来分析无效代码使用
	增加源文件建模工具
	多站点支持，多系统支持
	安装工具: 根据模板和配置，生成指定的index.php和.htaccess
	开发模式里，对模型内的输出和die/exit严格禁止. 但是在发布模式里，出于对效率的考虑，相关的屏蔽代码被撤掉 
	提供 access recall 功能，方便问题重现
	提供提供开发模式和发布模式的快速切换
	提供目录建立和权限检查工具
	提供缓存管理方式
	新建项目的生成过程要增强
	日志URL合并规则的管理。
	框架目录下的管理工具本身就是一个很好的demo
	管理工具，可以列出当前系统里已经部署的各个系统的列表
	管理工具，可以自动生成 config.default.php 的各配置项，不包括值，只包括key
	重新调整菜单的写法，使用顶栏菜单，放弃之前的侧边菜单方式。但要保留项目/页面关联菜单的做法。
	项目的目录权限检查工具
    controller 结束之前，应该检查 errorStatck    
    initAssign , 需要列出框架内使用的 config 参数表来
    libraries 里， cls.checkcode.php 还没有调通
    应该把 index.php 模板进一步封装，把日志之类的分支处理放到core类里面去
    开发版本的 getConfig 应该回写 cfg.default.php
	
	15. 提供class alias的功能，可以让多个( /mm/nn )URL中的不同的mm对应同一个 controller。
		当 该url中存在 nn 的时候， 调用 controller的nn方法，如果不存在，则调用controller->mm
		问题: 
			我能不能用 /admin/xx 的方式别名一个controller呢？
			系统的管理功能如何开发？ /admin/ccc/aaa 的url 是否是理想的选择呢？

	疑问:
		无法同时迁移 d hotel map 10jing 等多个频道，也就是说，数据模型无法在同一时间内建立，要想做出模型的重构工作
		就必须满足以下条件:
			1. 修改某一个数据库结构，同时可以修改几个频道的数据库模型，以保持一致性
			2. 要能同时修改几个频道的模型的一致性，就必须要几个频道都有了成型的mvc结构，那就要求几个频道都完成了入口及框架改造工作
			3. 几个频道同时修改模型，但又不能发布，会堆积很多问题难以解决
			
		另一种方案:
			1. 改造某一频道的mvc结构
			2. 逐步抽象 c 层及 m层，并逐步转到到新的数据库
			3. 提供一套数据库同步工具，保证数据有效
			4. 逐个频道逐渐迁移

    3.
    考虑把默认的数据库连接和smarty模板改为标准的库调用，从而让 index.php 的唯一入口不再依赖 public_connect和smarty 库
		
	

总结过程之后的重构步骤:
	0. 如何为插件的模板进行安装
	1. 建立  public/c 目录，建立 defaultcontroller.php 程序，并把public/index.php的内容建立为main方法
	2. 移动 commons 下的目录到 public下， commons/kernel 下的那几个类，已经在新的framework里默认包含了
		只需要在使用时调用初始化函数就是了
	3. 合并配置文件，
	4. 调试 main 方法直到通过
	5. 使用开发积累，对访问日志进行还原测试
	6. 逐步从原来的 classes入口分离新的 controller.action 到 c 目录下，并逐渐形成新的m层
	7. 需要增加 Alias的 controller支持， action 的alias 需要在 controller内实现
 	8. 在 dispatch 的过程中，如果 c不存在，返回404, 并设定js跳回 /, 如果 action不存在，则应返回302, 再跳 c.main
 	9. 为 controller 增加记录action执行时间的方法，
 	10. 为 module 的 mothod 增加记录执行时间的方法 
 	编写 defaultcontroller.php的 main 方法时的注意事项:
 		1. $objMain 的几个属性需要用 core 初始化，并赋值给 $objMain
 			$this->initDb();
 			$this->initTemplateEngine(THEMES_DIR.$core->getConfig('site')['theme'],
 									COMPILE_DIR.$core->getConfig('site')['theme']);
 			$objMain->db = $this->db;
 			$objMain->tpl = $this->tpl;
 			$objMain->_class_dir = CLASSES_DIR;
 			$objMain->_class_name = CLASS_NAME;
 			
 		2. 对于移到 main方法内的代码，重点检查  $CONFIG 的使用，替换为 $core->getConfig()
 		
 	应该由framework负责积累日志，进程结束时一次性输出，避免被打断
	开发阶段执行日志的格式:
	URL: /xxx/xxx
	start(url):microsecond,second
	start(controller->action):microsecond, second
	end(controller->action):microsecond, second
	end(url):microsecond,second
	
	有框架提供的系列工具:
	1. 安装工具: 根据模板和配置，生成指定的index.php和.htaccess
	2. 提供目录建立和权限检查工具
	3. 提供开发日志分析工具
	4. 提供提供开发模式和发布模式的快速切换
	5. 允许用户用自己的类库替换框架的标准库（如 smarty 换成其他版本或其他模板引擎，
	6. 内置框架升级功能. 使用git还是svn?
	7. 多站点支持，多系统支持
	8. 开发模式里，对模型内的输出和die/exit严格禁止. 但是在发布模式里，出于对效率的考虑，相关的屏蔽代码被撤掉 
	9. 管理工具，可以列出当前系统里已经部署的各个系统的列表
	10. 管理工具，可以自动生成 config.default.php 的各配置项，不包括值，只包括key
	11. 框架目录下的管理工具本身就是一个很好的demo
	12. acl 的支持如何做呢？ 
	13. 提供缓存管理方式
	14. 提供 access recall 功能，方便问题重现
	15. 提供class alias的功能，可以让多个( /mm/nn )URL中的不同的mm对应同一个 controller。
		当 该url中存在 nn 的时候， 调用 controller的nn方法，如果不存在，则调用controller->mm
		问题: 
			我能不能用 /admin/xx 的方式别名一个controller呢？
			系统的管理功能如何开发？ /admin/ccc/aaa 的url 是否是理想的选择呢？

	疑问:
		如何合理的迁移数据模型？需要增加一个转换程序吗？
		无法同时迁移 d hotel map 10jing 等多个频道，也就是说，数据模型无法在同一时间内建立，要想做出模型的重构工作
		就必须满足以下条件:
			1. 修改某一个数据库结构，同时可以修改几个频道的数据库模型，以保持一致性
			2. 要能同时修改几个频道的模型的一致性，就必须要几个频道都有了成型的mvc结构，那就要求几个频道都完成了入口及框架改造工作
			3. 几个频道同时修改模型，但又不能发布，会堆积很多问题难以解决
			
		另一种方案:
			1. 改造某一频道的mvc结构
			2. 逐步抽象 c 层及 m层，并逐步转到到新的数据库
			3. 提供一套数据库同步工具，保证数据有效
			4. 逐个频道逐渐迁移

2011年2月24日星期四
	现在要决定兼容性url,和新url规范
	http://map.lvren.cn/ditu/ankang 这样的url在功能上有问题，需要修复
			
2011年2月23日星期三
	完成第一个url的迁移  /city	
	classes/map.php 已经处理掉了
	遇到新的问题， 
		.htaccess 的规则里，可能有相当的一部分是跟当前频道无关的内容，怎么判断这些无关内容呢？
		同理，classes目录下对应的 *.php 也有相当一些是用不到的，怎么判断呢？
	已经把全部不可用的 rewrite 规则处理掉了，现在该处理有用的规则了


2011年2月15日星期二 
	在 /index.php 里，判断新框架下的 php文件和类是否存在，不存在，则返回默认控制器，这个默认控制器的
	默认方式，就是为了兼容原来有的分发方法而设定的
	如果其他项目里，有非唯一入口该怎么办？
	唯一入口的项目里，默认控制器按说应该由代码生成器生成，那就是说，我现在的代码应该是生成后被改写的了

2011年2月13日星期日 22:51
	最新进展: 阅读了CakePHP的手册和部分代码，CakePHP的整理结构跟目前我所要做的新框架的结构非常接近
	但，有些本质的不同:
		1. 基础路由和配置文件，我希望用安装的过程进行代码生成
		2. 我不要对模型进行那么多的封装，而是尽可能的自由，如果有必要，我可以提供代码生成式的类，待定
		3. Kernel 类要做的事情： 根据域名或者是baseurl来载入配置文件
		4. 根据配置文件的信息，判断加载带调试的基类或者发行版的基类
		5. 从CakePHP学到的东西: 可以根据正则，或者其他方法配置把2个不同的第一段指定给同一个Controller
			解答: 可以通过一个alias数组实现这个目标，这个数组应该在配置文件内填写吗？待议
		6. 提供几个接口:
			接管session
			提供数据库链接
			提供模板引擎的初始化
			提供点入日志的记录
			用另一个debug版本实现 controller.action 的调用记录, 需要用 register_shutdown_function 
			用另一个module的基类实现对 module.method 的调用记录，需要用 register_shutdown_function 
			提供缓存管理， 这个也需要 register_shutdown_function
			提供 expires etag last_modified 等http头的支持， register_shutdown_function
			需要自己进行 shutdown_functions 的管理


2011年2月11日星期五 11：15
	TODO:
	需要框架提供一个生成url的方法，支持rewrite的及不支持rewrite的2种url，都由该方法实现输出
	针对 map.lvren.cn 的 rewrite 规则，写rebuild_url 的函数
	rebuild_url 同时为 public 提供一个 c 层，提供一系列的入口改造
	CLASS_NAME 这个常量的定义，不应该在kernel这个类中使用
	对地图频道的摄像: 根据ip判断用户所在的城市，直接把地图缩放到该城市，以此降低地图页面的加载时间
	考虑同一套framework可以同时支持多个项目，无论多个项目是用的目录还是虚拟主机
	DONE：
		整合配置文件
	
	考虑作为一个框架，到底需要的是什么:
		1. kernel , 一个核心类，用于控制流程。 
			kernel 用于简化入口程序index.php的功能，并提供各种组合的实现方法
		2. 几个核心函数，用于实现基本功能: 
			rebuild_url // 解析rewrite过的url 
			genurl 		// 按照配置，生成url, 带rewrite和不带rewrite的2种方式都需要支持
			require
		3. session 接管的方法
		4. 点入 点出日志的支持


2011年2月3日星期四 12：42
	1. 对于柯志整套代码来说， index.php 所做的事情为:
		1. require 相关的配置，入口类，支持函数，
		2. 定义常量
			通用常量
			define('INIT_DIR',      realpath('../').'/');	
			define('COMMONS_DIR',   INIT_DIR.'commons/');
			define('FUNCTIONS_DIR', COMMONS_DIR.'functions/');
			define('KERNEL_DIR',    COMMONS_DIR.'kernel/');
			define('CLASSES_DIR',   COMMONS_DIR.'classes/');	
			define('LIBRARIES_DIR', COMMONS_DIR.'libraries/');
			define('SMARTY_DIR',    LIBRARIES_DIR.'smarty/');
			define('THEMES_DIR',    './themes/');
			define('COMPILE_DIR',   INIT_DIR.'themes_c/');

			前后文相关常量
			define('CLASS_NAME',$arrAction[0]);
			

		3. 可选步骤, 这三个步骤应该定义到项目的初始化阶段，而不是框架内
			做smarty赋值
			定义smarty参数
			定义数据库对象		
		4. 可行方案:
			为项目派生一个初始化类，继承自框架，专门用于做各种初始化的工作
			
		5. 应该给生成的.htaccess加一个选项，到底要不要rewrite,或者要不要生成.htaccess
		   不用 .htaccess的话，那url就应该是 : index.php?class=xx&method=bb&kkkkkk

2011年1月31日星期一 11:53
重构框架的策略如下:
	1. 在 commons 同级，建立 framework 目录，保证跟现有代码不会重复，方面迅速重构后面的几个项目
	2. 对 .htaccess 内的rewrite规则，和renew.php内的正则，考虑一种方法，进行快速转换
	3. map.lvren.cn 还没有 renew.php, 只需要再最后追加一个通用的url rewrite，不断的
  	   完善 url rebuild 就行了,增加 rebuildUrl函数 【完成】
	4. commons的子目录们: classes functions configs libraries data
		其中: 1.data 不应该出现在commons目录下，因为他跟框架无关。这里应该明确一个概念: public 为
				系统工作目录，commons（framework) 为框架代码库，即使commons(framework)目录为只
				读，系统也应该可以正确运行
			   在代码中，出现data这个目录的时候，引用方式比较多样化: 
			   		ROOT_DIR.'/data'
			   		dirname(__FILE__).'/data'
			   		dirname(__FILE__).'/../data'
			   		ROOT_DIR.'data'
			   		'./data/'
			   共计5种, 问题: 谁能告诉我这几个data目录到底是不是同一个目录？
			   
			 
			 2. libraries 应该是 public 目录下的，commons下的libraries应该是可选目录，而且应该
			 可以被用户载入的同名库覆盖，libraries目录下不应该有others这样的目录， smarty就保持
			 这样的目录名，在代码中使用 
			 	include('smarty/Smarty.class.php');
			 这样的写法就可以正确包含，是最好的选择，当用户需要用自己的smarty版本覆盖框架提供的版本时，
			 只需要在 public/libraries目录下增加 smarty 目录即可，或者在系统配置的include_path
			 里加入 smarty 目录所在的路径。
			 
			 3. configs 应该是 public目录下的文件，或者目录，而不应该在 commons目录下 【完成】
	4. 最后的2个 else 是有bug的，如果不存在的class是index或者index.main时， 
		header('location:/');
	   就会陷入死循环

2011年1月31日星期一 01:43
对柯志的框架，重新进行了构思:
	1. commons 应该是框架的目录，目录下分为 
		index.template.php install.php template.htaccess 4个文件
		classes libraries install   3个目录
		
		install.php 负责生成 
			1. public 目录下的 index.php 或者其他入口
			2. .htaccess 下的rewrite 规则
			3. set_include_path 的相关内容
			4. 生成默认资源如 favicon.ico 
			5. 生成好的各个入口程序的md5值，用 key:value的 方式保存到文本文件 files.md5 中	
		index.template.php 入口程序的模板
		template.htaccess  .htaccess文件的模板
		
	2.  相应的好处:
		框架代码与项目代码的分离，有助于框架代码的升级
		index.php .htaccess 文件的自动生成及md5值保存，可以方便到让用户重新生成新的入口程序，
		并且可以检测到用户是否对已经生成的文件做过变更，从而做出替换提示，或者自动备份
		
	3.  现有commons目录下划分的子目录： classes functions kernel 有些盲目，按照使用的方式， 
		classes是入口程序，基本扮演了控制器的角色， functions则实现了大量的逻辑和数据库的交互，
		其角色很像 module, 但跟classes之间区分并不明显， kernel目录，则很大程度上， 跟
		libraries目录的功能重叠，差异只是 libraries是第三方库，kernel是自己写的程序库，
		kernel并没体现出框架的核心价值来。
		
	新的问题:
		如果地图这个项目重建完成了，怎么样快速应用到其他的项目上去呢？这个需要考虑清楚了才能动手，
		从而实现对相关几个项目的快速重构

2011年1月31日星期一 01:30
新的思考结果:
	1. commons 应该是框架所在的目录，框架的全部代码独立于项目存在，从而方便升级和改进
		commons 下的所有代码都应该是项目无关的代码
	2. public 应该是项目所在的目录，其下所有的代码都应该是项目相关的目录
	3. 怎么让 public 和 commons 关联起来呢? 通过 rewrite到 public 下的 index.php吗？
	   在commons下，提供一个  index.example.php, public下的是个copy, 然后供用户随意改造
	   甚至重写
	

2011年1月30日星期日 23:38
已经发现的问题:
	1. public 目录到底是做什么事情的？ commons 目录到底是做什么的？ 为什么要划分这两个目录？
	2. 进入入口处写了 rewrite 规则，那为什么不一次写到位，直接写  
		index.php?class=index&method=main 
	   而是写成 index.php?act=index.main,然后到程序里再explode拆解一次呢？
	   这里要讲的原则是，尽可能在做某件事的时候，一次性做好，不要把同样的功能分散在多个地方重复的去做
	3. public下的index.php里，有如下代码:
	
		require_once( INIT_DIR.'commons/functions/fun.'.CLASS_NAME.'.php');
		
	   这里，既然functions 下的文件跟类文件同名，那就在类文件里进行包含就是了，不要让index.php代替
	   具体的class做做决定。让index.php只做好自己该做的事情就足够了
	4. 下一个问题是，为什么要分 cls.xxx.php和fun.xxx.php呢？同名，而且总是在一起出现，这一点的原
		因我还没有看到，或许问柯志一句会比较靠谱
	5. 为什么在根下有一个 data目录，在 commons下还有一个data目录，为什么？还好，public下没有出现
	 	第三个
		
	6. // TODO：一定要搞清楚的问题 
		public/mini.php 和  public/index.php 完全一样，为什么会有mini呢？	   
	  

2011年1月30日星期日 22:53
简单的记录一下重构过程中遇到的问题




路由: 正则表达式 支持过滤动态路径
路由: 分组 通过共用逻辑或中间件来处理有共同前缀的路径组
路由: 以上所有规则相结合而不产生冲突 这是一个高级且有用的功能，
路由: 自定义HTTP异常 指可以自行处理请求错误的情况。HTTP的错误状态码>=400
100%兼容net/http包 
中间件生态系统 
类Sinatra风格API 可以在运行时中注入代码来处理特定的 HTTP 方法(以及路径参数)
服务器程序: 自动启用HTTPS 框架的服务器支持注册及自动更新SSL证书来管理新传入的SSL/TLS连接(https)
服务器程序: 优雅关闭 当按下CTRL+C关闭终端应用程序时，服务器将等待(特定的超时时间)服务器程序: 多重监听框架的服务器支持自定义的net.Listener或使用多个http服务器和地址为web应用程序提供服务
完全支持HTTP/2 框架可以很好的处理https请求的http/2协议，并支持服务器push功能子域名 可以直接在Web应用中注入子域名的路径
会话(Sessions) 支持HTTP Sessions，且可以在自定义的handlers中使用sessions
Websockets 支持websocket通信协议，不同框架有不同的实现方式，
程序内嵌对视图(模版)的支持 通常情况下，你必须根据 Web 

视图引擎 框架支持模版加载、自定义及内建模版功能，节省开发时间
渲染: Markdown, JSON, JSONP, XML... 框架提供一个简单的方法来发送和自定义各种内容类型的响应
MVC Model-view-controller(MVC)模型是一种用于在计算机上实现用户界面的软件架构模式，
缓存   Web 缓存是一种用于临时存储(缓存)网页文档，如 HTML 页面和图像，来减缓服务器延时。一个 Web 缓存系统缓存网页文档，使得后续的请求如果满足特定条件就可以直接得到缓存的文档。Web 缓存系统既可以指设备，也可以指软件程序

文件服务器 可以注册一个(物理的)目录到一个路径，使得这个路径下的文件可以自动地提供给客户端

文件服务器: 内嵌入应用 

响应在发送前可以在整个生命周期中修改多次 

Gzip 

测试框架 

TypeScript转译器

在线编辑器

日志系统 
维护和自动更新 

---- 2020.1.8 从一个go语言web框架评测文章拿过来的一堆核心功能列表，看看能不能给tinycake做个评估


