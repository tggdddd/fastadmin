<?php
if (((int)PHP_VERSION) > 7) {
    $block = <<<'HIGHER_PHP'
    /** AUTOCURD 实体类注解
     * @param string $name 表名
     * @Annotation
     */
    #[Attribute(Attribute::TARGET_CLASS)]
    class Table
    {
        private $name;
        function getName()
        {
            return $this->name;
        }
        function __construct($table)
        {
            $this->name = $table;
        }
    }
    /** AUTOCURD 实体类属性注解  可不添加 则与数据库字段相同
     * @param string $field 属性名，如果不指定，默认为字段名
     * @param bool $ignore 是否忽略该字段
     * @param bool $id 是否为主键 未定义
     * @Annotation
     * 
     */
    #[Attribute(Attribute::TARGET_PARAMETER)]
    class Field
    {
        public $field;
        public $ignore;
        public $id;


        function __construct($field = null, $ignore = false, $id = false)
        {
            $this->field = $field;
            $this->ignore = $ignore;
            $this->id = $id;
        }
    }

    
    class CRUDEntity
    {
        private $table;
        private $fields = [];
        public function __construct($table = null, $fields = null)
        {
            $this->table = $table;
            $this->fields = $fields;
        }
        public function getTable(): string
        {
            return $this->table;
        }
        public function getFields(): array
        {
            return $this->fields;
        }
        public function setFields(array $fields): void
        {
            $this->fields = $fields;
        }
        public function getField(string $name): string
        {
            return $this->fields[$name];
        }
        public function addField(string $name, string $field = null, $id = null): void
        {
            $field = $field ?? $name;
            $this->fields[$name] = $field;
        }
        public function setTable($table): void
        {
            $this->table = $table;
        }
    }
 
/**
×AutoCRUD的工具类
*/
class AutoCRUDTools
{
    const map = [];

    /**
     * 获取对应的映射
     * @param object 定义Table属性的实体类
     * @throws \Exception
     * @return array
     */
    static function get_params(object $entity): CRUDEntity
    {
        $attribute = AutoCRUD::get($entity::class);
        $params = [];
        foreach ($attribute->getFields() as $key => $field) {
            if (isset($entity->$key)) {
                $params[$field] = $entity->$key;
            }
        }
        $res = new CRUDEntity();
        $res->setTable($attribute->getTable());
        $res->setFields($params);
        return $res;
    }
    /**
     * 获取表的定义 （由AUTOCRUD：：getParams()调用）
     * @param string $class 类名
     * @throws \Exception
     * @return CRUDEntity
     */
    static function get($class)
    {
        if (isset(AutoCRUD::map[$class])) {
            return AutoCRUD::map[$class];
        }
        $entity = AutoCRUD::reduce($class);
        $map = AutoCRUD::map;
        $map[$class] = $entity;
        return $entity;
    }
    /**
     * 获取表的定义 （由AUTOCRUD：：get()调用）
     * @param string $class 类名
     * @throws \Exception
     * @return CRUDEntity
     */
    static function reduce($class): CRUDEntity
    {
        $data = new CRUDEntity();
        // 获取表
        $reflect = new ReflectionClass($class);
        $tablecs = $reflect->getAttributes(Table::class);
        if (count($tablecs) != 1) {
            throw new Exception("Table注解必须存在");
        }
        $tables = $tablecs[0]->getArguments();
        if (count($tables) != 1) {
            throw new Exception("Table注解且必须有且仅有一个参数");
        }
        $table = $tables[0];
        $data->setTable($table);
        // 获取字段
        $fields = get_class_vars($class);
        foreach ($fields as $key => $value) {
            $reflect = new ReflectionProperty($class, $key);
            $fieldcs = $reflect->getAttributes(Field::class);
            $field = $key;
            if (count($fieldcs) > 0) {
                if (count($fieldcs) > 1) {
                    throw new Exception("字段注解只能存在一个");
                }
                $fieldc = $fieldcs[0];
                $fields = $fieldc->getArguments();
                if (isset($fields["ignore"])) {
                    continue;
                }
                $field = $fields["field"] ?? $key;
            }
            $data->addField($key, $field);
        }
        return $data;
    }
}
HIGHER_PHP;
    eval($block);
} else {
    class AutoCRUDTools
    {

    }
}
function get_conn($host = null, $port = null, $user = null, $password = null, $database = null)
{
    static $__host;
    static $__port;
    static $__user;
    static $__password;
    static $conn;
    $init = false;
    if (!empty($host)) {
        $__host = $host;
        $init = true;
    }
    if (!empty($port)) {
        $__port = $port;
        $init = true;
    }
    if (!empty($user)) {
        $__user = $user;
        $init = true;
    }
    if (!empty($password)) {
        $__password = $password;
        $init = true;
    }
    if ($init) {
        $conn = @mysqli_connect($__host, $__user, $__password, $database, $__port);

        //    error 
        if (!$conn || mysqli_errno($conn)) {
            throw new Exception($conn ? mysqli_error($conn) : "数据库连接失败");
        }
    }
    if (!empty($database)) {
        @mysqli_select_db($conn, $database);
        //    error 
        if (mysqli_errno($conn)) {
            throw new Exception(mysqli_error($conn));
        }
    }
    //配置UTF8编码
    $charset = mysqli_set_charset($conn, "UTF8");
    return $conn;
}

/**
 * @param array $array 赋值的列表
 * @param string $name 获取表单的name
 * @param string|null $key 存储的key
 * @param bool|null $trim 是否去除前后空白
 * @param bool|null $emptySkip 是否跳过 0 false null undefiled
 * @return void
 */
function get_post_value(array &$array, string $name, ?string $key = null, ?bool $trim = true, ?bool $emptySkip = true): void
{
    if ($_POST && isset($_POST[$name])) {
        $value = $_POST[$name];
        if (empty($value) and $emptySkip) {
            return;
        }
        $trim and $value = trim($value);
        $name = empty($key) ? $name : $key;
        $array[$name] = $value;
    }
}

/**
 * 获取POST表单的参数列表
 * @param array $array 赋值的列表
 * @param bool|null $trim 是否去除前后空白
 * @param bool|null $emptySkip 是否跳过 0 false null undefiled
 * @return void
 */
function get_all_post_value(array &$array, ?bool $trim = true, ?bool $emptySkip = true): void
{
    foreach ($_POST as $k => $v) {
        get_post_value($array, $k, $k, $trim, $emptySkip);
    }
}

/**用 / 拼接路径
 * @param string ...$path 路径
 * @return string 拼接后路径
 */
function path(string ...$path): string
{
    return implode("/", $path);
}

/**相对路径
 * @param $root string 根路径
 * @param $path string 绝对路径
 * @return string 返回相对根路径的路径
 */
function relative_path(string $root, string $path): string
{
    return str_replace("/", "\\", substr($path, strpos($path, $root) + strlen($root)));
}

/**获取相对根目录路径
 * @param $absPath string 绝对路径
 * @param ?bool $revert 通过相对路径获取绝对路径
 * @return string 返回相对根路径的路径
 */
function getAccessPath(string $absPath, ?bool $revert = false): string
{
    return $revert ? $_SERVER['DOCUMENT_ROOT'] . $absPath : relative_path($_SERVER['DOCUMENT_ROOT'], $absPath);
}

/**
 * 文件上传的返回结果
 */
class UploadSavaResult
{
    public $code;
    public $msg;
    public $success;
    public $path;

    /**
     * 返回失败结果
     * @param int|null $code 状态码
     * @param string|null $msg 消息
     * @return UploadSavaResult
     */
    public static function failure(?int $code = -1, ?string $msg = ""): UploadSavaResult
    {
        $result = new UploadSavaResult();
        $result->msg = $msg;
        $result->code = $code;
        $result->success = false;
        return $result;
    }

    /**
     * 返回成功结果
     * @param string|null $path 路径
     * @param string|null $msg 消息
     * @return UploadSavaResult
     */
    public static function success(?string $path = "", ?string $msg = ""): UploadSavaResult
    {
        $result = new UploadSavaResult();
        $result->path = $path;
        $result->msg = $msg;
        $result->code = 0;
        $result->success = true;
        return $result;
    }
}

/**保存所有上传的文件
 * @param string $path 保存路径
 * @return array|UploadSavaResult[] 每个key保存的结果
 */
function save_all_upload_files(string $path): array
{
    $result = [];
    if ($_FILES) {
        foreach (array_keys($_FILES) as $k => $v) {
            $result[$k] = save_upload_files($k, $path);
        }
        return $result;
    }
    return [UploadSavaResult::failure(-1, "没有上传的文件")];
}

/**
 * 保存上传的文件$_FILES
 * @param string $key name
 * @param string $path 保存路径
 * @param string|null $name 保存名称
 * @return UploadSavaResult
 */
function save_upload_files(string $key, string $path, ?string $name = null): UploadSavaResult
{
    $resultPath = [];
    if ($_FILES && !empty($_FILES[$key])) {
        $file = $_FILES[$key];
        if (!is_array($file['name'])) {
            $file["avatar"] = [$file["avatar"]];
            $file["name"] = [$file["name"]];
            $file["type"] = [$file["type"]];
            $file["tmp_name"] = [$file["tmp_name"]];
            $file["error"] = [$file["error"]];
            $file["size"] = [$file["size"]];
        }
        $len = count($file['name']);
//        验证文件是否正常
        for ($i = 0; $i < $len; $i++) {
            if (!$file['error'][$len] == 0) {
                switch ($file['error'][$len]) {
                    case UPLOAD_ERR_INI_SIZE:
                        return UploadSavaResult::failure(-1, "上传文件大小超过php.ini限制");
                    case    UPLOAD_ERR_FORM_SIZE:
                        return UploadSavaResult::failure(-1, "上传文件大小超过HTML表单限制");
                    case    UPLOAD_ERR_PARTIAL:
                        return UploadSavaResult::failure(-1, "上传文件不是全部上传");
                    case UPLOAD_ERR_NO_FILE:
                        return UploadSavaResult::failure(-1, "没有文件被上传");
                    case  UPLOAD_ERR_NO_TMP_DIR:
                        return UploadSavaResult::failure(-1, "找不到临时文件夹。");
                    case  UPLOAD_ERR_CANT_WRITE:
                        return UploadSavaResult::failure(-1, "文件写入失败。");
                    case   UPLOAD_ERR_EXTENSION:
                        return UploadSavaResult::failure(-1, "文件上传被扩展程序阻止。");
                    default:
                        return UploadSavaResult::failure(-1, "未知错误");
                }
            }
        }
        for ($i = 0; $i < $len; $i++) {
            if (empty($name)) {
                $name = date("YmdHis") . "-" . $file['name'][$i];
            }
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $temp = $file['tmp_name'][$i];
            $res = "";
            if (is_uploaded_file($temp)) {
                if (move_uploaded_file($temp, path($path, $name))) {
                    $res = $path . "/" . $name;
                }
            }
            $resultPath[] = $res;
        }
        return UploadSavaResult::success(implode(",", $resultPath));
    }
    return UploadSavaResult::failure(-1, "没有上传的文件");
}

class AutoCRUD extends AutoCRUDTools
{
    static function query()
    {
    }

    /** 使用AutoCURD插入数据
     * @param object $entity 具有Table属性的实体类
     * @return bool
     */
    static function insert(object $entity)
    {
        $crud = AutoCRUD::get_params($entity);
        $table = $crud->getTable();
        $params = $crud->getFields();
        $fields = array_keys($params);
        $values = array_values($params);
        $sql = "insert into {$table}(" . implode(",", $fields) . ") " . "values(" . substr(str_repeat(",?", count($values)), 1) . ")";
        $stmt = mysqli_prepare(get_conn(), $sql);
        if (count($values)) {
            $stmt->bind_param(str_repeat("s", count($values)), ...$values);
        }
        $stmt->execute();
        AutoCRUD::error($stmt->error);
        return $stmt->affected_rows;
    }

    /**mysql执行产生的错误
     * example：
     * if(0>=AutoCRUD::insert_sql($sql)){
     *    echo AutoCRUD::error();
     * }
     * */
    static function error(?string $error = null): string
    {
        static $global_error;
        if (isset($error)) {
            $global_error = $error;
        }
        return $global_error;
    }

    static function update(object $entity, object $query)
    {
    }

    static function remove()
    {
    }

    /**
     * 使用sql语句进行查询，可使用stmt进行值替换
     * @param string $sql
     * @param array $vals $sql以外所有参数
     * @return object|false
     */
    static function query_one_sql(string $sql, ...$vals)
    {
        $res = AutoCRUD::query_sql($sql, ...$vals);
        if ($res) {
            return $res[0];
        }
        return $res;
    }

    /**
     * 使用sql语句进行查询，可使用stmt进行值替换
     * @param string $sql
     * @param array $vals $sql以外所有参数
     * @return array|false
     */
    static function query_sql(string $sql, $bind = "", ...$vals)
    {
        $stmt = mysqli_prepare(get_conn(), $sql);
        if (!$stmt) {
            throw new Exception("stmt异常，mysqli_prepare执行失败");
        }
        if (count($vals)) {
            $stmt->bind_param($bind, ...$vals);
        }
        $res = $stmt->execute();
        if (!$res) {
            return false;
        }
        $result = $stmt->get_result();
        AutoCRUD::error($stmt->error);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * 使用sql语句进行查询，可使用stmt进行值替换
     * @param string $sql
     * @param array $vals $sql以外所有参数
     * @return bool
     */
    static function update_sql(string $sql, ...$vals)
    {
        $stmt = mysqli_prepare(get_conn(), $sql);
        if (count($vals)) {
            $stmt->bind_param(str_repeat("s", count($vals)), ...$vals);
        }
        $stmt->execute();
        AutoCRUD::error($stmt->error);
        return $stmt->affected_rows;
    }

    /**
     * 使用sql语句进行删除，可使用stmt进行值替换
     * @param string $sql
     * @param array $vals $sql以外所有参数
     * @return bool
     */
    static function remove_sql(string $sql, ...$vals)
    {
        $stmt = mysqli_prepare(get_conn(), $sql);
        if (count($vals)) {
            $stmt->bind_param(str_repeat("s", count($vals)), ...$vals);
        }
        $stmt->execute();
        AutoCRUD::error($stmt->error);
        return $stmt->affected_rows;
    }

    /**
     * 使用sql语句进行插入，可使用stmt进行值替换
     * @param string $sql
     * @param array $vals $sql以外所有参数
     * @return bool
     */
    static function insert_sql(string $sql, ...$vals)
    {
        $stmt = mysqli_prepare(get_conn(), $sql);
        if (count($vals)) {
            $stmt->bind_param(str_repeat("s", count($vals)), ...$vals);
        }
        $stmt->execute();
        AutoCRUD::error($stmt->error);
        return $stmt->affected_rows;
    }

    /**
     * 通过array的键值插入数据库
     * @param array $array 数据
     * @param string $table 插入表
     * @return int 影响的行数
     * @throws Exception 数据库操作异常
     */
    static function insert_by_map(array $array, string $table): int
    {
        $keys = array_keys($array);
        $values = array_values($array);
        $keystr = implode(",", $keys);
        $rp = substr(str_repeat(",?", count($keys)), 1);
        $sql = "insert into $table($keystr) values($rp);";
        $conn = get_conn();
        $stmt = mysqli_prepare($conn, $sql);
        $stmt->bind_param(str_repeat("s", count($values)), ...$values);
        $stmt->execute();
        AutoCRUD::error($stmt->error);
        return $stmt->affected_rows;
    }

    /**
     * 通过array的键值更新数据 需要有主键id
     * @param array $array 数据
     * @param string $table 插入表
     * @param bool|null $skipEmpty 空值跳过
     * @return int 影响的行数
     * @throws Exception 数据库操作异常
     */
    static function update_by_map_width_id(array $array, string $table, ?bool $skipEmpty = true): int
    {
        if (empty(trim($array['id']))) {
            throw new Exception("缺少主键id");
        }
        $id = $array['id'];
        unset($array['id']);
        if ($skipEmpty) {
            foreach ($array as $k => $v) {
                if (!empty(trim($v))) {
                    $array_r[$k] = trim($v);
                }
            }
            $array = $array_r;
        }
        $sets = "";
        foreach ($array as $k => $v) {
            $sets .= ", $k = ? ";
        }
        if (strlen($sets)) {
            $sets = " set" . mb_strcut($sets, 1);
        }
        $sql = "update $table $sets where id = $id;";
        $conn = get_conn();
        $stmt = mysqli_prepare($conn, $sql);
        $stmt->bind_param(str_repeat("s", count($array)), ...array_values($array));
        $stmt->execute();
        AutoCRUD::error($stmt->error);
        return $stmt->affected_rows;
    }
}

/***/

class QueryWrap
{
    private string $talbe;
    private ?array $fieldMap;
    private bool $translate = true;

    private array $where = [];
    private int $whereIndex = 0;
    private array $select = [];
    private string $havingSql = "";
    private array $order = [];
    private string $limitSql = "";

    public function __construct(string $classOrTable)
    {
        if ($classOrTable && class_exists($classOrTable)) {
            $map = AutoCRUD::get($classOrTable);
            $this->talbe = $map->getTable();
            $this->fieldMap = $map->getFields();
        } else {
            $this->talbe = $classOrTable;
        }
    }

    /**
     * @param string $field 查询字段 默认使用Entity配置的映射(通过setTranslate方法全局配置) 需要在QueryWrap实例化时指定Entity
     * @param bool|null $translate 是否使用Entity配置的映射
     * @param string|null $alias select 使用别名
     * @return $this
     */
    public function select(string $field, ?string $alias = null, ?bool $translate = null): QueryWrap
    {
        $this->select[] = $this->getField($field, $translate) . ($alias ? " as $alias" : "");
        return $this;
    }

    private function getField(string $field, ?bool $no)
    {
        if (($no ?? $this->translate) && isset($this->fieldMap)) {
            return $this->fieldMap[$field] ?? $field;
        }
        return $field;
    }

    /**
     * × 对于有指定Entity的类 可以使用filter($sqlFieldName)函数过滤字段
     * 若实例化为指定表格，则直接使用 × ， 不能在实体类上使用select
     */
    public function selectAll($filterFunction): QueryWrap
    {
        if (isset($this->fieldMap)) {
            $fields = [];
            foreach ($this->fieldMap as $field) {
                if ($filterFunction) {
                    if ($filterFunction($field)) {
                        $fields[] = $field;
                    }
                } else {
                    $fields[] = $field;
                }
            }
            array_push($this->select, ...$fields);
        } else {
            $this->select[] = "*";
        }
        return $this;
    }

    /**
     * @param string $field 查询字段 默认使用Entity配置的映射(通过setTranslate方法全局配置) 需要在QueryWrap实例化时指定Entity
     * @param mixed $val 值
     * @param bool|null $translate 是否使用Entity配置的映射
     * @return $this
     */
    public function eq(string $field, mixed $val, ?bool $translate = null): QueryWrap
    {
        $this->where[$this->whereIndex][] = "`{$this->getField($field,$translate)}` = \"$val\"";
        return $this;
    }

    /**
     * @param string $field 查询字段 默认使用Entity配置的映射(通过setTranslate方法全局配置) 需要在QueryWrap实例化时指定Entity
     * @param mixed $val 值
     * @param bool|null $translate 是否使用Entity配置的映射
     * @return $this
     */
    public function neq(string $field, string $val, ?bool $translate = null): QueryWrap
    {
        $this->where[$this->whereIndex][] = "`{$this->getField($field,$translate)}` != \"$val\"";
        return $this;
    }

    /**
     * @param string $field 查询字段 默认使用Entity配置的映射(通过setTranslate方法全局配置) 需要在QueryWrap实例化时指定Entity
     * @param mixed $val 值
     * @param bool|null $translate 是否使用Entity配置的映射
     * @return $this
     */
    public function like(string $field, string $val, ?bool $translate = null): QueryWrap
    {
        $this->where[$this->whereIndex][] = "`{$this->getField($field,$translate)}` like \"$val\"";
        return $this;
    }

    /**
     * @param string $field 查询字段 默认使用Entity配置的映射(通过setTranslate方法全局配置) 需要在QueryWrap实例化时指定Entity
     * @param mixed $val 值
     * @param bool|null $translate 是否使用Entity配置的映射
     * @return $this
     */
    public function notLike(string $field, string $val, ?bool $translate = null): QueryWrap
    {
        $this->where[$this->whereIndex][] = "`{$this->getField($field,$translate)}` not like \"$val\"";
        return $this;
    }

    /**
     * @param string $field 查询字段 默认使用Entity配置的映射(通过setTranslate方法全局配置) 需要在QueryWrap实例化时指定Entity
     * @param mixed $val 值
     * @param bool|null $translate 是否使用Entity配置的映射
     * @return $this
     */
    public function gt(string $field, string $val, ?bool $translate = null): QueryWrap
    {
        $this->where[$this->whereIndex][] = "`{$this->getField($field,$translate)}` > \"$val\"";
        return $this;
    }

    /**
     * @param string $field 查询字段 默认使用Entity配置的映射(通过setTranslate方法全局配置) 需要在QueryWrap实例化时指定Entity
     * @param mixed $val 值
     * @param bool|null $translate 是否使用Entity配置的映射
     * @return $this
     */
    public function ge(string $field, string $val, ?bool $translate = null): QueryWrap
    {
        $this->where[$this->whereIndex][] = "`{$this->getField($field,$translate)}` >= \"$val\"";
        return $this;
    }

    /**
     * @param string $field 查询字段 默认使用Entity配置的映射(通过setTranslate方法全局配置) 需要在QueryWrap实例化时指定Entity
     * @param mixed $val 值
     * @param bool|null $translate 是否使用Entity配置的映射
     * @return $this
     */
    public function lt(string $field, string $val, ?bool $translate = null): QueryWrap
    {
        $this->where[$this->whereIndex][] = "`{$this->getField($field,$translate)}` < \"$val\"";
        return $this;
    }

    /**
     * @param string $field 查询字段 默认使用Entity配置的映射(通过setTranslate方法全局配置) 需要在QueryWrap实例化时指定Entity
     * @param mixed $val 值
     * @param bool|null $translate 是否使用Entity配置的映射
     * @return $this
     */
    public function le(string $field, string $val, ?bool $translate = null): QueryWrap
    {
        $this->where[$this->whereIndex][] = "`{$this->getField($field,$translate)}` <= \"$val\"";
        return $this;
    }

    /**
     * @param string $field 查询字段 默认使用Entity配置的映射(通过setTranslate方法全局配置) 需要在QueryWrap实例化时指定Entity
     * @param bool|null $translate 是否使用Entity配置的映射
     * @param mixed $values 值
     * @return $this
     */
    public function in(string $field, ?bool $translate = null, mixed ...$values): QueryWrap
    {
        $field = $this->getField($field, $translate);
        foreach ($values as $k => $v) {
            $values[$k] = '"' . $v . '"';
        }
        $value = implode(",", $values);
        $temp = "`$field` in ($value)";
        $this->where[$this->whereIndex][] = $temp;
        return $this;
    }

    /**
     * @param string $field 查询字段 默认使用Entity配置的映射(通过setTranslate方法全局配置) 需要在QueryWrap实例化时指定Entity
     * @param bool|null $translate 是否使用Entity配置的映射
     * @param mixed $values 值
     * @return $this
     */
    public function notIn(string $field, ?bool $translate = null, mixed ...$values): QueryWrap
    {
        $field = $this->getField($field, $translate);
        foreach ($values as $k => $v) {
            $values[$k] = '"' . $v . '"';
        }
        $value = implode(",", $values);
        $temp = "`$field` not in ($value)";
        $this->where[$this->whereIndex][] = $temp;
        return $this;
    }

    /**
     * @param string $field 查询字段 默认使用Entity配置的映射(通过setTranslate方法全局配置) 需要在QueryWrap实例化时指定Entity
     * @param mixed $startVal 值
     * @param mixed $endVal 值
     * @param bool|null $translate 是否使用Entity配置的映射
     * @return $this
     */
    public function between(string $field, string $startVal, string $endVal, ?bool $translate = null): QueryWrap
    {
        $this->where[$this->whereIndex][] = "`{$this->getField($field,$translate)}` between \"$startVal\" and \"$endVal\"";
        return $this;
    }

    /**
     * @param string $field 查询字段 默认使用Entity配置的映射(通过setTranslate方法全局配置) 需要在QueryWrap实例化时指定Entity
     * @param mixed $startVal 值
     * @param mixed $endVal 值
     * @param bool|null $translate 是否使用Entity配置的映射
     * @return $this
     */
    public function notBetween(string $field, string $startVal, string $endVal, ?bool $translate = null): QueryWrap
    {
        $this->where[$this->whereIndex][] = "`{$this->getField($field,$translate)}` not between \"$startVal\" and \"$endVal\"";
        return $this;
    }

    /**
     * @param string $sql sql语句
     * @return $this
     */
    public function whereSql(string $val): QueryWrap
    {
        $this->where[$this->whereIndex][] = $val;
        return $this;
    }

    public function orderBy(string $field, ?bool $desc = false, ?bool $translate = null): QueryWrap
    {
        $this->order[] = $this->getField($field, $translate) . ($desc ? " desc" : "");
        return $this;
    }

    public function limit(int $offset, ?int $length = null): QueryWrap
    {
        $this->limitSql = "limit $offset" . ($length ? ",$length" : "");
        return $this;
    }

    public function having(string $sql): QueryWrap
    {
        $this->havingSql = $sql;
        return $this;
    }

    /** 分隔条件   默认的条件查询为and
     * 在SQL中，AND的优先级比OR高
     * example： where （a=1 and b=2 ）or（c=3 and d = 4）
     * tip：括号不存在
     * */
    public function or(): QueryWrap
    {
        $this->where[] = [];
        $this->whereIndex++;
        return $this;
    }

    public function getSql()
    {
        $select = implode(",", $this->select);
        $table = $this->talbe;
        $where = "";
        for ($i = 0; $i <= $this->whereIndex; $i++) {
            $temp = $this->where[$i];
            if (count($temp)) {
                $whereTemp[] = implode(" and ", $temp);
            }
        }
        if (isset($whereTemp) && count($whereTemp)) {
            $where = implode(" or ", $whereTemp);
        }
        if (strlen($where)) {
            $where = "where " . $where;
        }
        $having = $this->havingSql;
        $order = "";
        if (count($this->order)) {
            $order = "order by " . implode(",", $this->order);
        }
        $limit = $this->limitSql;
        $sql = <<<EOL
            select $select
                from $table
                    $where
                    $having
                    $order
                    $limit
        EOL;
        return $sql;
    }

    /**配置是否使用字段映射*/
    public function setTranslate(bool $flag): QueryWrap
    {
        $this->translate = $flag;
        return $this;
    }
}

?>