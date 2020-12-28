<?php 
/**
 * Woski - A simple PHP framework for the realm
 * @author Clinton Nzedimma <clinton@woski.xyz>
 *  @package ORM
*/ 


namespace Woski\ORM;

use Woski\ORM\WoskiORM;

abstract class WoskiModel extends WoskiORM
{

	function __construct($pdo)
	{
		 parent::__construct($pdo);
		 if(isset($this->has))$this->initHasAssociation();
	}

}
?>