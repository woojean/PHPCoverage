# PHPCoverage说明文档
PHPCoverage是一款基于xdebug实现的PHP代码覆盖率统计工具，可以方便地对PHP项目的代码覆盖率情况进行统计。


### 使用
PHPCoverage的使用非常简单，在下载了PHPCoverage的项目代码至本地后，只需在项目的主入口处（通常是项目的index.php文件的开头处）引用插桩文件，并添加插桩代码即可。

##### 插桩代码的示例如下：
`index.php`

```javascript
<?php

// 引入插桩文件，例如我的本地PHPCoverage项目位置在：/vagrant/PHPCoverage处，则如下引用
require_once '/vagrant/PHPCoverage/src/Woojean/PHPCoverage/Injecter.php';

// 插桩
Woojean\PHPCoverage\Injecter::Inject([
	'log_dir'=>'/vagrant/logs',
	'ignore_file'=>'/vagrant/PHPCoverage/demo/example.ignore',
	'is_repeat' => true 
]);

// ...
```

##### 参数说明：
###### log_dir
log_dir应该是一个PHP具有写权限的目录的路径（绝对路径），`用于生成覆盖率统计文件及最终的覆盖率报告`。

###### ignore_file
ignore_file`用于指定需要忽略掉的文件`，比如第三方的代码、框架文件以及其他不想关注的文件。
该文件`使用PHP数组描述`，在PHPCoverage项目的ignores文件夹中包含一个示例文件example.ignore，内容如下：
```
<?php

return [
	"/vagrant/www/cbd_wechat/vendor",
	"/vagrant/www/cbd_wechat/library"
];
```

###### is_repeat
is_repeat可以指定为ture或false，用于控制是否进行`叠加测试`。
当is_repeat为false时，将不进行叠加测试，这意味着每次开始执行测试之前，都会清空log_dir目录中的所有文件（即之前的测试记录）。
当is_repeat为true时，将进行叠加测试，这意味着多次测试生成的文件将会被保留，并最终进行合并分析。
如果想对单次PHP请求的代码覆盖率情况进行统计，应该将is_repeat设为false。
如果想要统计多次代码执行累计的代码覆盖率情况，则应该讲is_repeat设为true。
也可以手工清除log_dir中的内容以保证之前的测试结果不会被之后的测试一起统计。


##### 报告说明：
当测试结束后，会在插桩时指定的log_dir目录下生成覆盖率统计文件和报告文件，其中index.html文件为报告的入口文件：

 ![image](https://github.com/woojean/PHPCoverage/raw/master/imgs/files.jpg)

打开index.html文件后，内容如下：

 ![image](https://github.com/woojean/PHPCoverage/raw/master/imgs/reporter.jpg)


文件顶部为本次测试的总体统计信息：

 ![image](https://github.com/woojean/PHPCoverage/raw/master/imgs/sum.jpg)



文件左侧为本次测试覆盖到的所有文件的列表（不含在ignore_file忽略的文件），点击文件列表中的任意文件，将在右侧展示该文件的覆盖情况。

其中灰色的行为不可执行代码：

 ![image](https://github.com/woojean/PHPCoverage/raw/master/imgs/unexec.jpg)


其中淡黄色的行为可执行代码但未覆盖的代码：

 ![image](https://github.com/woojean/PHPCoverage/raw/master/imgs/uncove.jpg)


绿色的行为被覆盖的可执行代码：

 ![image](https://github.com/woojean/PHPCoverage/raw/master/imgs/covered.jpg)


同时，在左侧文件列表中，针对不同的代码覆盖程度，也给出了不同颜色区分的标记：

 ![image](https://github.com/woojean/PHPCoverage/raw/master/imgs/colored.jpg)


### 通过Composer安装使用

##### Composer包名：
`woojean/php-coverage`

##### 插桩代码的示例如下：
`index.php`

```javascript
<?php

// 插桩
Woojean\PHPCoverage\Injecter::Inject([
	'log_dir'=>'/vagrant/logs',
	'ignore_file'=>'/vagrant/PHPCoverage/demo/example.ignore',
	'is_repeat' => true 
]);

// ...
```
参数配置及说明见上文。




