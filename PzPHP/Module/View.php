<?php
class PzPHP_Module_View extends PzPHP_Wrapper
{
	/**
	 * @param $view
	 * @param array $parameters
	 * @return string
	 * @throws PzPHP_Exception
	 */
	public function render($view, array $parameters = array())
	{
		$file = PzPHP_Config::get('VIEWS_DIR').$view.'.php';

		if(file_exists($file))
		{
			$PZPHP = $this->pzphp();

			if(!empty($parameters))
			{
				extract($parameters);
			}

			ob_start();

			require $file;

			$content = ob_get_clean();

			ob_end_clean();

			return $content;
		}
		else
		{
			throw new PzPHP_Exception('View "'.$file.'" not found!', PzPHP_Helper_Codes::VIEW_NOT_FOUND);
		}
	}
}
