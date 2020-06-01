<?php
class Accueil extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->helper('url');
		$this->load->library('form_validation');
		$this->load->library('email');

	}

	public function index()
	{
		$this->session->sess_destroy();
		$this->homePage();
	}
	function homePage()
	{
		$this->load->view('template/View_template');
		$this->load->view('template/View_template_center');
		$this->load->view('View_accueil');
	}
	public function validate_email($email)	{
		$this->load->model('Model_connexion');

		if ($this->Model_connexion->getCount($email)>0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	public function login()

	{

	 	$this->load->helper('form');
	 	$this->load->model('Model_connexion');
	 	$this->form_validation->set_rules('Email', 'Email', 'valid_email|required|callback_validate_email');
		$this->form_validation->set_rules('Pwd', 'Mot de passe', 'required|alpha_numeric');
	 	$this->form_validation->set_message('validate_email', '{field} existe pas dans la base.');

	 	if ($this->form_validation->run()) {
	 		$email = $this->input->post('Email');
	 		$password = $this->input->post('Pwd');
	 		if ($this->Model_connexion->getCount($email)>0){
	 			$datadb = $this->Model_connexion->getAll($email);

	 			if (password_verify($password, $datadb->password)) {
	 				session_start();
	 				$session_data = array(
	 					'login' => $datadb->login,
	 					'email' => $email,
	 					'id' => $datadb->id
	 				);
	 				$this->session->set_userdata($session_data);
	 				redirect(MenuPrincipal);

	 			} else {
	 				$this->load->view('template/View_template');
	 				$this->load->view('template/View_template_center');

	 				$this->load->view('errors/View_login_error');
	 			}
	 	}else{

	 			$this->load->view('template/View_template');
	 			$this->load->view('template/View_template_center');
	 			$this->load->view('errors/View_login_error');
	 		}
	 	} else {
	 		if ($this->form_validation->error_array() == null) {
	 			$this->load->view('template/View_template');
	 			$this->load->view('template/View_template_center');
	 			$this->load->view('View_login');
	 		} else {
	 			$this->load->view('template/View_template_center');
	 			$this->load->view('template/View_template');

	 			$this->load->view('errors/View_login_error');

	 		}
	 	}
	}

	public function register()
	{

		$this->load->model('Model_connexion');
		$this->load->library('form_validation');

		$this->form_validation->set_rules('Login', 'Login', 'required|alpha_numeric');
		$this->form_validation->set_rules('Email', 'Email', 'valid_email|required|is_unique[user.email]');
		$this->form_validation->set_rules('Pwd', 'Mot de passe', 'required|alpha_numeric|min_length[8]');
		$this->form_validation->set_rules('Pwdv', 'Mot de passe de confirmation', 'required|alpha_numeric|matches[Pwd]');

		$this->form_validation->set_message('is_unique', '{field} est déjà présent dans la base.');

		if ($this->form_validation->run()) {

			$login = ($this->input->post('Login'));
			$email = $this->input->post('Email');
			$password = $this->input->post('Pwd');
			$pwdhash = password_hash($password, PASSWORD_DEFAULT);
			$clé= $this->Model_connexion->createKeyForUser();

			$data = array(
				'login' => $login,
				'email' => $email,
				'password' => $pwdhash,
				'clé'=> $clé
			);
			$this->email->to($data['email']);
			$this->email->from('Projet_alban_vasile@iut-fbleau.fr');
			$this->email->subject('Vérification de votre compte.');
			$this->email->message('Bonjour ! Veuillez cliquer sur ce lien afin d\'activer votre compte . https://dwarves.iut-fbleau.fr/~stelzle/Projetwims2.1_stelzle_ciocoiu/index.php/Accueil/activateUser/'.$data['clé']);
			$this->email->send();
			$this->Model_connexion->createNewUser($data);
			redirect('', 'refresh');
		}else {
			if($this->form_validation->error_array() == null){
				$this->load->view('template/View_template');
				$this->load->view('template/View_template_center');
				$this->load->view('View_register');
			}else{
				$this->load->view('template/View_template_center');
				$this->load->view('template/View_template');

				$this->load->view('errors/View_register_error');

			}

			}
		}
		public function activateUser($key){
			$this->load->model('Model_connexion');
			$this->Model_connexion->ActivateAccount($key);

		}
		public function join(){
			$this->load->view('template/View_template');
			$this->load->view('template/View_template_center');
			$this->load->view('View_join');

		}

}

?>
