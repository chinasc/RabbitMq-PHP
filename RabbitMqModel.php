<?php

/**
 * rabbit mq message queue
 * client
 *
 */
 
class RabbitMqModel extends Model
{
    public static $amqpConn = null;
    
    public $routeKey = '';
    
    public $queueName = '';
    
    public $exchangeName = '';
    
    public $contentEncoding = 'UTF-8';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * 创建连接
     * 单例模式
     * 
     */
    public function amqp_connection() {
        
        if (is_object(static::$amqpConn)) return static::$amqpConn;
        
        $connection = new AMQPConnection($this->setConnConf()); 
        $connection->connect(); 
    
        if (!$connection->isConnected()) { 
             echo "Cannot connect to the broker"; 
        }
        
        return static::$amqpConn = $connection;
    }
    
    /**
     * 设置连接mq服务器参数
     * 
     */
    public function setConnConf()
    {
        return $conf = [
            'host' => '172.16.113.83',  
            'port' => '5672',  
            'login' => 'gshopper',  
            'password' => 'izene123', 
            'vhost'=>'/' 
        ];
    }
    
    /**
     * 创建信道
     * 
     */
    public function createChannel()
    {
        $channel = new AMQPChannel($this->amqp_connection());
        return $channel;
    }
    
    /**
     * 创建交换机
     * 
     */
    public function createExchange()
    {
        $exchange = new AMQPExchange($this->createChannel());//创建exchange
        $exchange->setName($this->exchangeName);             //创建名字
        $exchange->setType(AMQP_EX_TYPE_DIRECT);             //类型
        $exchange->setFlags(AMQP_DURABLE);                   //持久化
        return $exchange;
    }
    
    /**
     * 创建队列
     * 
     */
    public function createQueue()
    {
        $queue = new AMQPQueue($this->createChannel());
        $queue->setName($this->queueName);                   //创建队列名，不存在则新建
        $queue->setFlags(AMQP_DURABLE | AMQP_AUTODELETE);
        $queue->bind($this->exchangeName, $this->routeKey);  //队列绑定routeKey
    }
    
    /**
     * 设置数据
     * 对json中的json数据进行转义，方便java读取
     */
    public function setData($data)
    {
        if (!is_array($data))
        {
            $data = array($data);
        } else {
            foreach ($data as $key => $value)
            {
                if (is_array($value))
                {
                    $data [$key] = json_encode($value);
                }
            }
        }
        
        return $this->data = json_encode($data);
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * 释放链接
     * 
     */
    public function disConnect()
    {
        $this->amqp_connection()->disconnect;
    }
    
    /**
     * 提交
     * 
     */
    public function submit()
    {
        try {
            // 创建连接
            $con = $this->amqp_connection();
            // 创建交换机
            $exchange = $this->createExchange();
            // 创建信道
            $channel = $this->createChannel();
            // 开始事物
            $channel->startTransaction();
            // 创建队列
            //$this->createQueue();
            // 消息推送
            $exchange->publish($this->getData(), $this->routeKey, AMQP_NOPARAM, ['content_encoding' => $this->contentEncoding]);
            // 提交事物
            $channel->commitTransaction();
            $this->disConnect();
            return true;
        } catch (AMQPConnectionException $e) {
            // TODO
            return false;
        }
    }
}

/**
 * 日志
 * 
 */
class LogMe
{
    public function cacheMe()
    {
        $ua = Yii::$app->request->userAgent;
        $ga = Yii::$app->request->get('ua');
        if ($ua=='LocalApiRobot' or $ga=='LocalApiRobot' or Yii::$app->request->isAjax)
        {
            $jsonData = $params['jsonData'];
            is_array($jsonData) or $jsonData=[];
            // log
            if (Yii::$app->params['saveApiLog'])
            {
                $txt = "\n------------------------------------------------------------------";
                $txt .= "\n@@@时间：".date('Y-m-d H:i:s');
                $txt .= "\n@@@来源：".Yii::$app->request->getUserIP();
                $txt .= "\n@@@方法：".Yii::$app->request->getMethod();
                $txt .= "\n@@@目标：".Yii::$app->request->getUrl();
                $txt .= "\n@@@变量(GET)：\n".print_r($_GET,true);
                $txt .= "@@@变量(POST)：\n".print_r($_POST,true);
                $txt .= "@@@返回(ARR)：\n".print_r($jsonData,true);
                $txt .= "@@@返回(JSON)：\n".print_r(Json::encode($jsonData),true);
                $txt .= "\n******************************************************************";
                $file = Yii::getAlias('@runtime/logs/api.log');
                fclose(fopen($file,'a+'));
                $_fo = fopen($file,'rb');
                $old = fread($_fo,1024*1024);
                fclose($_fo);
                file_put_contents($file,$txt.$old);
            }
            // json
            Yii::$app->response->clear();
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->content = Json::encode($jsonData);
            Yii::$app->response->send();
            Yii::$app->end();
        }
        else
        {
            return $this->render($view, $params);
        }
    }
}