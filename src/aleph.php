<?php

/**
 * Aleph
 *
 * A simple PHP framework for very small sites.
 *
 * @package aleph
 * @author lastguest@gmail.com
 * @url https://github.com/lastguest/aleph
 * @version 1.0.0
 * @copyright Stefano Azzolini - 2014 - http://dreamnoctis.com
 */

/*+ MINIMIZE */

/**
 * Default options
 */

options('templates.dir',      __DIR__.'/templates');
options('templates.cache.dir',    __DIR__.'/cache');
options('templates.cache.enabled',  false);


/**
 * DEPENDENCY CONTAINERte
 */

function service($name, $value=null){
  static $services = array();
  if (is_callable($value)) {
    $services[$name] = $value;
  } else {
    if (isset($services[$name])) {
      return call_user_func_array($services[$name],array_slice(func_get_args(),1));
    }
  }
}

/**
 * OPTIONS
 */

function options($key=null, $value=null){
  static $options = array();
  if (is_null($key)) return $options;
  if (is_null($value)) {
    return isset($options[$key]) ? $options[$key] : null;
  } else {
    return $options[$key] = is_callable($value) ? call_user_func($value) : $value;
  }
}

/**
 * REQUEST
 */

function request(){
  $headers = array();
  $uri = rtrim(parse_url(filter_input(INPUT_SERVER,'REQUEST_URI'),PHP_URL_PATH),'/')?:'/';
  if ($baseuri = trim(options('app.baseuri'),'/')) $uri = preg_replace("~^/$baseuri~", '', $uri)?:'/';
  foreach($_SERVER as $k=>$v) {
    if (substr($k, 0, 5) == 'HTTP_') $headers[str_replace('_','-',(substr($k,5)))] = $v;
  }
  $method = strtolower(filter_input(INPUT_SERVER,'REQUEST_METHOD'));
  return array(
    'method'    => /*filter('request.method',*/$method/*)*/,
    'uri'       => filter('request.uri',$uri),
    'query'     => filter_input_array(INPUT_GET),
    'post'      => filter_input_array(INPUT_POST),
    'cookies'   => filter_input_array(INPUT_COOKIE),
    'headers'   => $headers,
  );
}


function quit($status=500){
  if (function_exists('http_response_code')){
    http_response_code($status);
  } else {
    header("Status: $status",true,$status);
  }
  exit;
}

/**
 * RESPONSE
 */

function response(/* ... */){
  static $headers = array(), $fragments = array();
  $params = func_get_args();
  $action = count($params) ? strtolower(array_shift($params)) : null;
  if (is_null($action)){
    return array(
      'headers' => array_values($headers),
      'body'    => implode('',$fragments),
    );
  } else {
    switch($action){
      case 'header':
          $headers[$params[0]] = "{$params[0]}: {$params[1]}";
        break;
      case 'delete':
          $headers = $fragments = array();
        break;
      case 'clear': case 'clean':
          $fragments = array();
        break;
      case 'append':
          $fragments[] = $params[0];
        break;
      case 'start':
           ob_start();
        break;
      case 'stop':
          if (ob_get_level() > 1) {
            $fragments[] = ob_get_contents();
            ob_end_clean();
          }
        break;
      case 'append':
          $fragments[] = $params[0];
        break;
    }
  }
}

/**
 * EVENTS
 */
function event($action, $event, $callback){
  static $events = array();
  switch(strtolower($action)){
    case 'on' :
      $events[$event][] = $callback;
      break;
    case 'off' :
      if ($callback) {
        unset($events[$event][$callback]);
      } else {
        unset($events[$event]);
      }
      break;
    case 'trigger' :
      if (isset($events[$event]) && isset($events[$event])) {
        if (!is_array($callback)) $callback = array();
        foreach($events[$event] as $handler) call_user_func_array($handler, $callback);
      }
      break;
  }
}

/**
 * EMAIL
 */
function email($from, $to, $subject, $body, array $extra_headers=array()){
  $time     = $_SERVER['REQUEST_TIME'];
  $headers  = array();
  foreach(array_merge(array(
    "From"                        => "{$from}",
  ),array(
    "Reply-To"                    => "{$from}",
    "Return-Path"                 => "{$from}",
  ), $extra_headers, array(
    "Content-type"                => "text/html; charset=\"UTF-8\"",
    "Content-Transfer-Encoding"   => "7bit",
    "Date"                        => "" . date('r', $time),
    "Message-ID"                  => "<$time".md5($time)."@".$_SERVER['SERVER_NAME'].">",
    "MIME-Version"                => "1.0",
  )) as $k => $v) $headers[] = "{$k}: {$v}";
  $head     = implode("\r\n",$headers);
  die($head);
  $subject  = '=?UTF-8?B?' . base64_encode(str_replace("\n", '', $subject)) . '?=';
  foreach((array)$to as $recipient){
    $_to            = str_replace("\n", '', $recipient);
    $results[$_to]  = mail($_to,$subject,$body,$head);
  }
  return $results;
}

/**
 * FILTERS
 */

function filter($name, $callback = null){
  static $filters = array();
  if (is_callable($callback)) {
    // Set filter handler
    $filters[$name][] = $callback;
  } else {
    // Run filter
    $value = $callback;
    if (empty($filters[$name])) return $value;
    foreach($filters[$name] as $handler) $value = call_user_func($handler, $value);
    return $value;
  }
}

function on($event, $callback){
  event('on', $event, $callback);
}

function off($event, $callback = null){
  event('off', $event, $callback);
}

function trigger($event /* ... */){
  event('trigger', $event, array_slice(func_get_args(), 1));
}

function triggerOnce($event /* ... */){
  event('trigger', $event, array_slice(func_get_args(), 1));
  event('off', $event, null);
}

/**
 * TEMPLATE
 */

function template($name, $params=array()){
  static $templates = array();
  $use_cache      = !!options('templates.cache.enabled');
  $template_dir   = rtrim(options('templates.dir')?:__DIR__.'/templates','/');
  $name           = trim($name,'/');
  $template_file  = "$template_dir/$name.html";

  if ($use_cache) {
    $cache_dir      = rtrim(options('templates.cache.dir')?:__DIR__.'/cache','/');
    if (!is_dir($cache_dir)) @mkdir($cache_dir);
    $template_cache = "$cache_dir/$name.php";

    if (file_exists($template_cache)) {
      $templates[$template_file] = file_get_contents($template_cache);
    }
  }

  if (!isset($templates[$template_file])) {
    $getParam = function($tok){return function_exists(trim(strtok($tok,'(')))?$tok:'@$'.trim(str_replace('.','->',$tok));};
    $compiled = array();
    $state = 'html';
    $tokens = preg_split('~({{|}}|{%|%}|{#|#}|{&|&})~m',file_get_contents($template_file),-1,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    foreach ($tokens as $tok){
      if ($state == 'skip') {
        if ($tok == '#}') $state = 'html';
        continue;
      }
      switch ($tok) {
        case '{{':
          $state      = 'echo';
          $compiled[] = '<?=';
          break;
        case '}}':
          $state      = 'html';
          $compiled[] = '?>';
          break;
        case '{#':
          $state      = 'skip';
          break;
        case '#}':
          $state      = 'html';
          break;
        case '{%':
          $state      = 'code';
          $compiled[] = '<?php ';
          break;
        case '%}':
          $state      = 'html';
          $compiled[] = ' ?>';
          break;
        case '{&':
          $state      = 'php';
          $compiled[] = '<?php '."\n";
          break;
        case '&}':
          $state      = 'html';
          $compiled[] = "\n".'?>';
          break;
        default:
          switch ($state) {
            case 'skip':
              break;
            case 'code':
              $keywords = array_filter(preg_split('~\s+~',$tok));
              $statement = array_shift($keywords);
              switch ($statement) {
                case 'for':
                  if (strpos($keywords[2],'..')!==false){
                    $what = 'range('.preg_replace('~\s*\.\.\s*~', ',', $keywords[2]).')';
                  } else {
                    $what = $getParam($keywords[2]);
                  }
                  $compiled[] = 'foreach('.$what.'?:array() as $'.$keywords[0].'){';
                  break;
                case 'end':
                  $compiled[] = '}';
                  break;
              }
              break;
            case 'echo':
              $state      = 'html';
              $compiled[] = $getParam($tok);
              break;
            case 'php':
              $compiled[] = trim($tok);
              break;
            case 'html':
              $compiled[] = $tok;
              break;
          }
          break;
      }
    }
    $templates[$template_file] = implode('',$compiled);

    if ($use_cache) {
      file_put_contents($template_cache, $templates[$template_file]);
    }

  }

  $source = $templates[$template_file];

  return call_user_func(function() use ($source, $params){
    extract($params);
    ob_start(); eval('?>'.$source); $___BUFF___ = ob_get_contents(); ob_end_clean();
    return $___BUFF___;
  });
}

/**
 * ROUTES
 */

register_shutdown_function(function(){
  route();
  $response = response();
  foreach ($response['headers'] as $value) header($value,true);
  echo $response['body'];
  trigger('app.exit');
});

function get($path, $callback){
  route('get',$path,$callback);
}

function post($path, $callback){
  route('post',$path,$callback);
}

function put($path, $callback){
  route('put',$path,$callback);
}

function delete($path, $callback){
  route('delete',$path,$callback);
}

function route($method='@', $path='', $callback=null){
  static $routes = array();
  if ($method == '@') {
    $request = request();
    if (empty($routes[$request['method']])) {
      trigger(404);
      quit(404);
    }
    foreach ($routes[$request['method']] as $pattern => $route) {
      if (preg_match('#^/'.$pattern.'/?$#',$request['uri'],$captures)){
        array_shift($captures);
        trigger('route.before',$route,$captures);
        response('start');
        $results = call_user_func_array($route['callback'], $captures);
        response('stop');
        if (is_array($results) || is_object($results)) {
          response('delete');
          response('header','Content-Type','application/json');
          response('append',json_encode($results, JSON_NUMERIC_CHECK));
        } else {
          echo $results;
        }
        trigger('route.after',$route);
        return;
      }
    }
    trigger(404) or quit(404);
  } else {
   if($path) {
    $method = strtolower(trim($method));
    $path = preg_replace_callback('#(:\w+)#', function($m){
              return '([^/]+)';
           }, str_replace('.','\.',trim($path,'/')));
    $routes[$method][$path] = array(
        'callback'  => $callback ?: function(){},
      );
    }
  }
}

/**
 * ==========================
 * DATABASE
 * ==========================
 */

/**
 * Database module
 */
function database(/* ... */){
    $args = func_get_args();
    $action = array_shift($args);
    switch ($action) {
        case 'init':
            // Prepare new database service
            service('database', function() use ($args) {
                $pdo = new PDO(
                               $args[0],
                               isset($args[1])?$args[1]:'',
                               isset($args[2])?$args[2]:'',
                               array(
                                PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
                                PDO::ATTR_EMULATE_PREPARES => false,
                              )
                );
                trigger('database.init',$pdo);
                return $pdo;
            });
        break;
    }
}

/**
 * Standard database uses in-memory sqlite
 */
service('database', function(){
    $pdo = new PDO('sqlite::memory:',array(
        PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ));
    trigger('database.init',$pdo);
    return $pdo;
});

/**
 * Fetch all results from sql query, via callback or as a whole.
 */
function sql_each($sql, $callback, $params=array()){
    try {
        $db = service('database');
        $statement = $db->prepare($sql);
        $statement->execute($params);
        if (is_callable($callback)){
            while ($row = $statement->fetchObject()){
                call_user_func($callback, $row);
            }
        } else {
            return $statement->fetchAll(PDO::FETCH_CLASS);
        }
    } catch (PDOException $e) {
        trigger('database.error',$e,$sql,$params);
        return false;
    }
}

/**
 * Fetch single row from sql results
 */
function sql_row($sql, $params=array()){
    try {
        $db = service('database');
        $statement = $db->prepare($sql);
        $statement->execute($params);
        return $statement->fetchObject();
    } catch (PDOException $e) {
        trigger('database.error',$e,$sql,$params);
        return false;
    }
}

/**
 * Fetch column from sql results
 */
function sql_value($sql, $params=array(), $column=0){
    try {
        $db = service('database');
        $statement = $db->prepare($sql);
        $statement->execute($params);
        return $statement->fetchColumn($column);
    } catch (PDOException $e) {
        trigger('database.error',$e,$sql,$params);
        return false;
    }
}

/**
 * Execute raw sql code
 */
function sql($sql, $params=array()){
    try {
        $db = service('database');
        $statement = $db->prepare($sql);
        return $statement->execute($params);
    } catch (PDOException $e) {
        trigger('database.error',$e,$sql,$params);
        return false;
    }
}
