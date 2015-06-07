<?php

class IndexController extends Controller
{
	function actionIndex()
	{
		$objects = Test::findAll();
		$this->render('index', array(
			'data' => $objects
		));
	}
	
	function actionMessage($id)
	{
		$object = Test::findByPk($id);
		$this->render('message', array(
			'data' => $object
		));
	}
	
	function actionAddMessage($nick, $msg)
	{
		$test = new Test();
		$test->Name = $nick;
		$test->Message = $msg;
		$test->save();
		$this->redirect('/');
	}
	
	function actionFuckMessage($id)
	{
		$test = Test::findByPk($id);
		$test->Message = 'FUCKED UP';
		$test->save();
		$this->redirect('/');
	}
	
	function actionDeleteMessage($id)
	{
		$test = Test::findByPk($id);
		$test->remove();
		$this->redirect('/');
	}
}
