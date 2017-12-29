<?php
	require_once("vue/vue.php");
	require_once("modele/modele.php");
	function ctlAcceuil()
	{
		afficherAccueil();
	}

	class ExceptionMontatnDepasse extends Exception
	{

	}

	class ExceptionLogin extends Exception
	{

	}

	class ExceptionIdNonTrouveGF extends Exception
	{

	}

	class ExceptionIdNonTrouveSynthese extends Exception
	{

	}

	class ExceptionClientNonTrouve extends Exception
	{
	}

	class ExceptionClientExiste extends Exception{}

	class ExceptionEmployeExisteDeja extends Exception
	{

	}

	class ExceptionCategorie extends Exception
	{

	}
	function ctlPayerDerniere()
	{
		$int = getInterEnAttente($_SESSION['client']->idClient);
		if (!empty($int)) {
			payerInter($int[0]->code);
		}
	}

	function ctlPayerInter($checkInter)
	{
		if (!empty($checkInter)) {
			foreach ($checkInter as $inter) {
				payerInter($inter);
			}
		}
	}

	function ctlDiffererInter($checkInter)
	{
		$montant = 0;
		$codes = [];
		foreach ($checkInter as $inter) {
			//parcours de tous les checkbox et recuperer ceux en attente
			if (!empty($interEnAtt = ctlGetEnAttente($inter))) {
				$montant = $montant + $interEnAtt->montant;
				$codes[] = $inter;
			}
		}
		if ($_SESSION['client']->montantMax - ($_SESSION['diffEnCours'] + $montant) >= 0) {
			foreach ($codes as $inter) {
				differer($inter);
			}
		} else {
			throw new ExceptionMontatnDepasse("Montant autorisé est depassé");
		}
	}

	function ctlGetEnAttente($inter)
	{
		return getEnAttente($inter);
	}

	function ctlAfficherPageCorrespondante($login, $motdepasse)
	{
		$employe = ctlChercherIdentifiantsEmploye($login, $motdepasse);
		$_SESSION['empl'] = $employe;
		switch ($employe->categorie) {
			case'agent':

				afficherAccueilAgent($employe);
				break;
			case'mecanicien':
				break;
			case'directeur':
				afficherAccueilDirecteur($employe);
				break;
		}
	}

	function ctlChercherIdentifiantsEmploye($login, $motdepasse)
	{
		if ($employe = getEmploye($login, $motdepasse)) {
			return $employe;
		} else {
			throw  new ExceptionLogin("Login ou mot de passe incorrect");
		}
	}

	function ctlGestionFinanciere($id)
	{
		if ($client = ctlGetClient($id)) {
			$_SESSION['client'] = $client;
			$diff = getInterDiff($id);
			$enatt = getInterEnAttente($id);
			$sommediff=0;
			foreach ($diff as $intd) {
				$sommediff += $intd->montant;
			}
			$_SESSION['diffEnCours'] = $sommediff;
			afficherGestionFinanciere($diff, $enatt);
		} else {
			throw new ExceptionIdNonTrouveGF("Id non trouvé");
		}
	}

	function ctlMettreAJourClient($infos)
	{
		$modifs=array();
		$client =(array)$_SESSION['client'];
		foreach($infos as $key=>$val){

			if($key != 'modifierClient' && $val != $client[$key]){
				$modifs[$key]=$val;
			}
		}
		if(!empty($modifs)){
			modifierClient($client['idClient'],$modifs);
		}
	}

	function ctlSyntheseClient($id)
	{
		if($client=ctlGetClient($id)) {
			$_SESSION['client'] = $client;
			$interventions = getInterventionsPasses($id);
			$diff = getInterDiff($id);
			$sommediff = 0;
			foreach ($diff as $intd) {
				$sommediff += $intd->montant;
			}
			afficherSynthese($client,$interventions,$sommediff,$client->montantMax-$sommediff);
		}else throw new ExceptionIdNonTrouveSynthese("Id non trouvé");
	}

	function ctlGetClient($id)
	{
		$client = getClient($id);
		return $client ? $client : false;
	}

	function ctlGetIdClient($nom, $dateNaiss)
	{
		if ($client = getIdClient($nom, $dateNaiss)) {
			$_SESSION['rechercheIdClient'] = $client;
		} else {
			throw new ExceptionClientNonTrouve("Aucun client " . $nom . " né le " . $dateNaiss . " trouvé.");
		}

	}

	function ctlAjouterClient(){
		$infos = array();
		foreach ($_POST as $key => $val) {
			if ($key != 'ajouterClient') {
				if ($key == 'dateNaiss') {
					$infos[$key] = date($val);
				} else {
					$infos[$key] = $val;
				}
			}
		}
		if(!ctlExisteClient($infos['nom'],$infos['prenom'],$infos['dateNaiss'])){
			ajouterClient($infos);
		}else throw new ExceptionClientExiste("Le client existe déjà.");
	}

	function ctlExisteClient($nom,$prenom,$date){
		return !empty($client=existeClient($nom,$prenom,$date));
	}

	function ctlCreerCompte()
	{
		if (in_array($_POST['categorie'], array("mecanicien", "directeur", "agent"))) {
			if ($empl = chercherEmploye($_POST['nomEmploye'], $_POST['login']) == null) {
				creerCompte($_POST['nomEmploye'], $_POST['login'], $_POST['motDePasse'], $_POST['categorie']);
			} else {
				throw new ExceptionEmployeExisteDeja("Employe avec ce nom ou login existe deja");
			}
		} else {
			throw new ExceptionCategorie("Categorie non autorise");
		}
	}

	function CtlErreur($erreur)
	{
		afficherErreurLogin($erreur);
	}