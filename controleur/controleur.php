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
	class ExceptionFormation extends Exception
	{
	}
	class ExceptionPriseRdv extends Exception
	{
	}
	class ExceptionMontantNegatif extends Exception{}
	class ExceptionIdNonTrouveGF extends Exception
	{

	}
	class ExceptionTypeInterExiste extends  Exception{

	}
	class ExceptionIdNonTrouveSynthese extends Exception
	{

	}

	class ExceptionClientNonTrouve extends Exception
	{
	}

	class ExceptionClientExiste extends Exception{}
	class ExceptionEmploye extends Exception{}

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

				afficherAccueilAgent($employe,getToutLesMecanos(),chercherToutTypeIntervention());
				break;
			case'mecanicien':
				afficherAccueilMecanicien(getMecanicien($_SESSION['empl']->nomEmploye),getToutLesMecanos());
				break;
			case'directeur':
				afficherAccueilDirecteur($employe);
				break;
		}
	}
	function ctlSetPlaningMecanos($nomMecano,$dateDebutSemaine){
			//cherche le planning de mecanicien pour chaque jour
		// de la semaine a partir de la date passe en parametre

			//creation d'un objet DateTime modifiable
			$date=  new \DateTime(date('Y-m-d', strtotime($dateDebutSemaine)));


			$jours=array();

			for ($i=0; $i<7;$i++){
				$x=getJournee($nomMecano,$date->format('Y-m-d'));
				$jours[$i]=$x;
				//le jour suivant
				$date->modify('+ 1 days');
			}
		$_SESSION['PlaningSemaineMecano']=$jours;
		/*foreach ($jours as $d){
				foreach ($d as $j)
				echo $j->nomTI.' j '.$j->dateIntervention.' a '.$j->heureIntervention.'<br>';
				echo 'jour op<br>';
		}*/

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
	function ctlPlanningUnMecano($nom,$date){
		$j=getJournee($nom,$date);

	afficherPlanningMecanicien(getMecanicien($nom),$j);
	}
	function ctlSyntheseClient($id)
	{
		if($client=ctlGetClient($id)) {
			$_SESSION['client'] = $client;
			if($_SESSION['empl']->categorie=='agent')
			$interventions = getInterventionsPasses($id);
			elseif($_SESSION['empl']->categorie=='mecanicien')
				$interventions = getInterventionParIdCode($id,$_POST['code']);
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
			throw new ExceptionClientNonTrouve("Aucun client  trouvé.");
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
				$_SESSION['nouveauCompte']='Nouveau compte de '.$_POST['nomEmploye'].' a été créé';
			} else {
				throw new ExceptionEmployeExisteDeja("Employe avec ce nom ou login existe deja");
			}
		} else {
			throw new ExceptionCategorie("Categorie non autorise");
		}
	}


	function ctlChercherToutLesEmploye(){
		return  chercherToutLesEmploye();
	}

	function ctlChercherUnEmploye($nom){
		if(empty($nom))
			throw new ExceptionEmploye("Veuillez entrer le nom d'employe");
		if(($employe=chercherUnEmploye($nom))!=null){
			return $employe;
		}
		else{
			throw new ExceptionEmploye("Pas d'employe avec ce nom");
		}
	}

	function ctlModifierEmploye(){
		$modifications='';
		$employe=(array)chercherUnEmploye($_POST['nomEmploye']);
		foreach ($employe as $cle => $valeur)
			if(!empty($_POST[$cle])){
				if($_POST[$cle]!=$valeur){
					modifierEmploye($cle,$_POST[$cle],$_POST['nomEmploye']);
					//sauvgarder les modifs
					$modifications .=ucfirst($cle).' de '.$_POST['nomEmploye'].' a ete modifie. ';
				}

					if(!empty($_SESSION['TousLesEmploye'])){
						unset($_SESSION['TousLesEmploye']);
					}
					$_SESSION['EmployeDirecteur']=chercherUnEmploye($_POST['nomEmploye']);
					//alors je vais afficher celui qui a ete modifier avec le message special
				if(!empty($modifications))
					$_SESSION['EmployeMidifie']='Employe a bien été mofifie. '.$modifications;
				else
					$_SESSION['EmployeMidifie']='Aucun changement n\'a été éffectué';

			}else {
				throw new ExceptionEmploye("L'un des champs est vide veuillez garder tout les champs remplis");
			}

	}
	function ctlChercherTypesIntervention(){
		return chercherToutTypeIntervention();
	}
	function ctlSupprimerEmploye(){
		if($_POST['nomEmploye']==$_SESSION['empl']->nomEmploye){
			echo 'lol';

			throw new ExceptionEmploye("Vous pouvez pas supprimer votre propre compte sinon vous etes coincé");
		}

		else{
			supprimerEmploye($_POST['nomEmploye']);
			$_SESSION['EmployeSupprime']=$_POST['nomEmploye'].' a bien ete supprimé.';
			unset($_SESSION['TousLesEmploye']);
		}

	}
	function ctlModifierIntervention(){
		$_SESSION['InterModifie']=$_POST['nomTI'];
		if($_POST['montant']<0){
			throw new ExceptionMontantNegatif("Le montant doit être positif");
		}
		modifierIntervention($_POST['nomTI'],$_POST['montant'],$_POST['listePieces']);

	}

	function 	ctlSupprimerIntervention(){
		supprimerIntervention($_POST['nomTI']);
	}
	function ctlCreerIntervention(){
		if(($intervention=chercherTypeIntervention($_POST['nomTI']))!=null){
			throw new ExceptionTypeInterExiste("Intervention avec ce nom existe deja");
		}else{
			creerTypeIntervention($_POST['nomTI'],$_POST['montant'],$_POST['listePieces']);
		}
	}



	function ctlJournee($mecanicien){
		afficherJournee(getJournee($mecanicien));
	}

	function ctlFormation($date,$heure){
		if($heure<8||$heure>19)
			throw new ExceptionFormation("heure doit etre entre 8 et 19 h, sinon je viens pas :p");
		$employe=$_SESSION['empl']->nomEmploye;
		$inter=getInter($employe);
		$formation=getFormation($employe);
		foreach ($inter as $i){
			if ($i->dateIntervention == $date && $i->heureIntervention == $heure){
				throw new ExceptionFormation("Formation impossible (Intervention ou Formation déjà présente)");
			}
		}
		foreach ($formation as $f){
			if ($f->dateForm == $date && $f->heureForm == $heure){
				throw new ExceptionFormation("Formation impossible (Intervention ou Formation déjà présente)");
			}
		}



		ajouterFormation($date,$heure,$employe);
		$_SESSION['formationInsere']='Formation a été ajouté le '.$date.' à '.$heure;
	}
	function ctlPrendreRendezVous($nomTI,$date,$heureIntervention,$nomMecano,$idClient){
		if($heureIntervention>19 || $heureIntervention<8){
			throw new ExceptionPriseRdv("Tout les mecaaniciens travaillent entre 8 et 19 h");

		}
		if(($form=getFormationParDateHeure($nomMecano,$date,$heureIntervention))!=null){
			throw new ExceptionPriseRdv("Il existe deja une formation a cette heure pour ce mecanicien");
		}
		if(($interv=getInterventionParDateHeure($nomMecano,$date,$heureIntervention))!=null){
			throw new ExceptionPriseRdv("Il existe deja une intervention a cette heure pour ce mecanicien");
		}
		prendreRdv($nomTI,$date,$heureIntervention,$nomMecano,$idClient);
		$intercree=getInterventionParDateHeure($nomMecano,$date,$heureIntervention);

		$_SESSION['succesRendezVousListePieces']='Pieces a fournir: '.getInterventionParIdCode($idClient,$intercree->code)->listePieces;



	}

	function CtlErreur($erreur)
	{
		afficherErreurLogin($erreur);
	}