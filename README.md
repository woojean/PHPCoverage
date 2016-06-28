# PHPCoverage (简洁易用的PHP代码覆盖率统计工具)

## 使用
### 使用Composer安装使用







### 不使用Composer
在项目的主入口处引用插桩文件，并添加插桩代码，如下：

```javascript
// 引入插桩文件，例如我的本地PHPCoverage项目位置在：/vagrant/www/github/PHPCoverage
require '/vagrant/www/github/PHPCoverage/Injecter.php';

// 插桩
PHPCoverage_Inject([
    'log_dir'=>'/vagrant/logs/PHPCoverage',
    'ignore_file'=>'/vagrant/www/github/PHPCoverage/ignores/example.ignore',
    'is_repeat' => true 
]);
```

### 参数说明
##### log_dir
##### ignore_file
##### is_repeat



