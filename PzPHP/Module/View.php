<?php
class PzPHP_Module_View extends PzPHP_Wrapper
{
	/**
	 * @param       $view
	 * @param array $parameters
	 * @param null  $overrideViewDir
	 *
	 * @return string
	 * @throws PzPHP_Exception
	 * @throws Exception
	 */
	public function render($view, array $parameters = array(), $overrideViewDir = null)
	{
		$file = (empty($overrideViewDir)?PzPHP_Config::get('VIEWS_DIR'):$overrideViewDir).$view.'.php';

		if(file_exists($file))
		{
			$PZPHP = $this->pzphp();

			if(!empty($parameters))
			{
				extract($parameters);
			}

			try
			{
				ob_start();

				require $file;

				$content = ob_get_clean();

				return $content;
			}
			catch(Exception $e)
			{
				ob_end_clean();

				throw new Exception($e->getMessage(), $e->getCode());
			}
		}
		else
		{
			throw new PzPHP_Exception('View "'.$file.'" not found!', PzPHP_Helper_Codes::VIEW_NOT_FOUND);
		}
	}
}
