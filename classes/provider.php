<?php

abstract class Provider
{
	public abstract function begin();
	public abstract function end();
	public abstract function count();
	public abstract function getNext();
}
