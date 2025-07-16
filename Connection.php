<?php

class PDOConnection extends PDO
{
    public function __construct($dsn = null, $user = null, $password = null, $options = array())
    {
        try {

            global $Globals;

            $default_options = array(
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            );
            
            $tempDNS = "mysql:host=".$Globals["DB_SEC_HOST"].";port=".$Globals["DB_SEC_PORT"].";dbname=".$Globals["DB_SEC_SCHEMA"].";charset=utf8";

            $dsn = $dsn ? $dsn : $tempDNS;
            $user = $user ? $user : $Globals["DB_SEC_USER"];
            $password = $password ? $password : $Globals["DB_SEC_PASS"];
            $options = array_replace($default_options, $options);

            parent::__construct($dsn, $user, $password, $options);

        } catch (PDOException $e) {
            throw new Exception($e->getMessage(),1);
        }
    }

    public function setBuffer()
    {
        $this->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    }

    public function run($sql, $args = null)
{

    try {
        
        $result = null;

        if (!$args) {
            $stmt = $this->query($sql);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC); ### Retorna um array associativo com os resultados ###
        } else {
            $stmt = $this->prepare($sql);
            $stmt->execute($args);

            ### Verifica se a consulta retorna múltiplos conjuntos de resultados ###
            if ($stmt->columnCount() > 0) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        return $result;
    } catch (PDOException $e) {
        throw new Exception($e->getMessage(), 1);
    }
}
}