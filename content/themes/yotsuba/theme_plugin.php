<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');


class Theme_Plugin_yotsuba extends Plugins_model
{

	function __construct()
	{
		parent::__construct();
	}


	function initialize_plugin()
	{
		// use hooks for manipulating comments
		$this->plugins->register_hook($this, 'fu_post_model_process_comment_greentext_result', 8, '_greentext');
		$this->plugins->register_hook($this, 'fu_post_model_process_internal_links_html_result', 8,
			'_process_internal_links_html');

		$this->plugins->register_hook($this, 'fu_post_model_process_crossboard_links_html_result', 8,
			'_process_crossboard_links_html');

		$this->plugins->register_hook($this, 'fu_chan_controller_before_page', 3, 'page');
		$this->plugins->register_hook($this, 'fu_chan_controller_before_gallery', 3, function(){ show_404(); });

		$this->plugins->register_controller_function($this, array('chan', '(:any)', 'board'), 'board');
	}


	function _greentext($html)
	{
		return '\\1<span class="quote">\\2</span>\\3';
	}


	function _process_internal_links_html($data, $html, $previous_result = NULL)
	{
		// a plugin with higher priority modified this
		if(!is_null($previous_result))
		{
			return array('return' => $previous_result);
		}

		return array('return' => array(
			'tags' => array('<span class="quote">', '</span>'),
			'hash' => '',
			'attr' => 'class="quotelink"',
			'attr_op' => 'class="backlink"',
			'attr_backlink' => 'class="backlink"',
		));
	}


	function _process_crossboard_links_html($data, $html, $previous_result = NULL)
	{
		// a plugin with higher priority modified this
		if(!is_null($previous_result))
		{
			return array('return' => $previous_result);
		}

		return array('return' => array(
			'tags' => array('<span class="unkfunc">', 'suffix' => '</span>'),
			'backlink' => ''
		));
	}


	/**
	 * @param int $page
	 */
	public function page($page = 1)
	{
		return array('parameters' => array($page, FALSE, array('per_page' => 24, 'type' => 'by_thread')));
	}


	public function board()
	{
		if ($this->input->post())
		{
			switch ($this->input->post('mode'))
			{
				case 'Delete':
					foreach ($this->input->post('post') as $idx => $doc_id)
					{
						$this->post->delete(get_selected_radix(),
							array(
								'doc_id' => $doc_id,
								'password' => $this->input->post('pwd')
							)
						);
					}
					break;

				case 'Report':
					$report = new Report();

					foreach ($this->input->post('post') as $idx => $doc_id)
					{
						$report->add(array(
							'board' => get_selected_radix()->id,
							'doc_id' => $doc_id,
							'reason' => $this->input->post('reason')
						));
					}
					break;

				default:
					show_404();
			}

			$this->theme->set_layout('redirect');
			$this->theme->set_title(__('Redirecting...'));
			Chan::_set_parameters(
				array(
					'title' => __('Redirecting...'),
					'url' => site_url(get_selected_radix()->shortname)
				)
			);
			$this->theme->build('redirection');
			return TRUE;
		}

		show_404();
	}


}
