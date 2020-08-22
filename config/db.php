<?php 
/**
* Woski - A PHP framework
 * @author Clinton Nzedimma <clinton@woski.xyz>
 *
 * @package Woski

 * This config file should contain the connection parameters to your database
 * Your models use this configuration.
 * Your alternative database adapters can use this configuration if your import it
 **/	

return [
	"DB_HOST" => $_ENV['DB_HOST'],
	"DB_NAME" => $_ENV['DB_NAME'],
	"DB_USERNAME" => $_ENV['DB_USERNAME'],
	"DB_PASSWORD"=> $_ENV['DB_PASSWORD'],
	"DB_CONNECTION"=> $_ENV['DB_CONNECTION']
];
	
