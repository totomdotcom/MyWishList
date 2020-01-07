<?php

namespace wishlist\controler;

use \Illuminate\Database\Capsule\Manager as DB;
use wishlist\model\Liste;
use wishlist\model\Item;
use wishlist\model\User;
use wishlist\model\MessagesListes;
use wishlist\view\VuePagePerso;
use wishlist\authentification\Authentification;
use wishlist\exception\AuthException;
use wishlist\view\VueConfirmation;

class pagePersoControler
{

	public function confirmerSuppr()
	{
		$v = new VueConfirmation();
		$v->render();
	}

	public function getPPerso()
	{

		if (isset($_SESSION['session'])) {

			$u = User::where('user_id', '=', $_SESSION['session']['user_id'])->first();
			$v = new vuePagePerso();
			$v->render($u);
		} else {
			$c = new IdentifiantControler();
			$c->getConnexion();
		}
	}

	public function connexion()
	{
		try {
			$app = new \Slim\Slim;
			$datas = $app->request();

			$mail = $datas->post("Mail");
			$pass = $datas->post("Passe");


			Authentification::authenticate($mail, $pass);

			//			$u = User::where('mail', '=', $mail)->first();
			//			if (!is_null($u)) {
			//Déjà fait par authenticate
			//				Authentification::loadProfile($u->user_id);
			$this->getPPerso();
			//			}
		} catch (AuthException $ae) {

			echo "Email ou mot de passe invalide<br>";
		}
	}

	public function supprimerCompte()
	{

		if (isset($_SESSION['session'])) {

			//destruction dans la table user de la ligne de l'utilisateur
			$u = User::where('user_id', '=', $_SESSION['session']['user_id'])->first();

			$l = $u->listes()->get();

			foreach ($l as $value) {
				$i = $value->item()->get();
				$m = $value->messages()->get();
				foreach ($i as $item) {
					$item->delete();
				}
				foreach ($m as $message) {
					$message->delete();
				}
				$value->delete();
			}
			$u->delete();
		}
		Authentification::disconnect();
		$v = new VuePagePerso();
		$v->compteSupprimer();
	}

	public function modifProfile()
	{

		$app = new \Slim\Slim;
		$u = User::where('user_id', '=', $_SESSION['session']['user_id'])->first();

		$modif = VuePagePerso::PAGE_PERSO;

		$datas = $app->request();

		$prenom = $datas->post("Prenom");
		if ($prenom != $u->prenom) {
			$prenom = filter_var($prenom, FILTER_SANITIZE_SPECIAL_CHARS);
			$u->prenom = $prenom;

			$_SESSION['session']['prenom'] = $prenom;

			$u->save();
		}

		$pass1 = $datas->post("Passe1");
		$pass2 = $datas->post("Passe2");
		if ($pass1 != "" && $pass2 != "") {
			$pass1 = filter_var($pass1, FILTER_SANITIZE_SPECIAL_CHARS);
			$pass2 = filter_var($pass2, FILTER_SANITIZE_SPECIAL_CHARS);
			if ($pass1 == $pass2) {
				$u->hash = password_hash($pass1, PASSWORD_DEFAULT, ['cost' => 12]);

				Authentification::disconnect();

				$modif = VuePagePerso::RECONNEXION;
			}
		}

		ItemControler::ajoutImg($u);
		$u->save();

		$v = new VuePagePerso();
		$v->confirmation($modif);
	}

	public function allCreateur() {

		$lCreateur = array();

		$lListe = Liste::where('public', '=', 1)->distinct()->get();

		foreach ($lListe as $liste) {
			$user = User::where('user_id','=',$liste->user_id)->first();
			$lCreateur[] = $user;
		}

		$v = new VuePagePerso();
		$v->afficherCreateur($lCreateur);
	}
}