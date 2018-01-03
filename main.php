<?php
	try {
		require_once("controleur/controleur.php");
		session_start();

		if (isset($_POST['connexion'])) {
			ctlAfficherPageCorrespondante($_POST['login'], $_POST['motdepasse']);

		} elseif (isset($_POST['gestionF'])) {
			ctlGestionFinanciere($_POST['idclientGF']);

		} elseif (isset($_POST['payerDer'])) {
			ctlPayerDerniere();
			ctlGestionFinanciere($_SESSION['client']->idClient);

		} elseif (isset($_POST['payer'])) {
			ctlPayerInter($_POST['checkInter']);
			ctlGestionFinanciere($_SESSION['client']->idClient);

		} elseif (isset($_POST['differer'])) {
			ctlDiffererInter($_POST['checkInter']);
			ctlGestionFinanciere($_SESSION['client']->idClient);

		} elseif (isset($_POST['rechercheID'])) {
			$nom = $_POST['nomclient'];
			$dateNaiss = date($_POST['dateNaiss']);
			ctlGetIdClient($nom, $dateNaiss);
			ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
		} elseif (isset($_POST['deco'])) {
			session_destroy();
			ctlAcceuil();
		} elseif (isset($_POST['accueil'])) {
			ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
		} elseif (isset($_POST['synthese'])) {
			ctlSyntheseClient($_POST['idclient']);
		} elseif (isset($_POST['modifierClient'])) {
			ctlMettreAJourClient($_POST);
			ctlSyntheseClient($_SESSION['client']->idClient);
		} elseif (isset($_POST['creerCompte'])) {
			ctlCreerCompte($_POST['nomEmploye'], $_POST['login'], $_POST['motDePasse'], $_POST['categorie']);
			ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
		} elseif (isset($_POST['ajouterClient'])) {
			ctlAjouterClient();
			ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
		}
		elseif (isset($_POST['afficherToutLesComptes'])) {
			$_SESSION['TousLesEmploye']=ctlChercherToutLesEmploye();
			ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
		}
		elseif (isset($_POST['chercherEmployeParNom'])) {
			$_SESSION['EmployeDirecteur']=ctlChercherUnEmploye($_POST['nomEmploye']);
			ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
		}
		elseif (isset($_POST['modifierEmploye'])) {
			ctlModifierEmploye();
			ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
		}
		elseif (isset($_POST['supprimerEmploye'])) {
			ctlSupprimerEmploye();
			//si il est pas directeur;
			ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
		}
		elseif (isset($_POST['creerIntervention'])) {
			ctlCreerIntervention();
			//si il est pas directeur;
			ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
		}
		elseif (isset($_POST['afficherToutLesTypeIntervention'])) {
			$_SESSION['TypesDIntervention']=ctlChercherTypesIntervention();
			//si il est pas directeur;
			ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
		}
		elseif (isset($_POST['modifierIntervention'])) {
			ctlModifierIntervention();
			$_SESSION['TypesDIntervention']=ctlChercherTypesIntervention();

			//si il est pas directeur;
			ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
		}
		elseif (isset($_POST['supprimerIntervention'])) {
			ctlSupprimerIntervention();
			//si il est pas directeur;
			ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
		}
		elseif (isset($_POST['planningJournee'])) {
			ctlInterJournee();
		} elseif (isset($_POST['saisirFormation'])) {
			ctlFormation($_POST['dateFormation'],$_POST['heureFormation']);
			ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
		}
		elseif (isset($_POST['afficherPlaningMecano'])) {

			ctlPlanningUnMecano($_POST['meca'],$_POST['datePlanning']);

		}
		elseif (isset($_POST['syntese'])) {

			ctlSyntheseClient($_POST['idClient']);

		}
		else {
			ctlAcceuil();
		}
	} catch (ExceptionLogin $e) {
		$msg = $e->getMessage();
		CtlErreur($msg);
	} catch (ExceptionMontatnDepasse $e) {
		$msg = $e->getMessage();
		$_SESSION['erreurMontant'] = $msg;
		ctlGestionFinanciere($_SESSION['client']->idClient);
	} catch (ExceptionClientNonTrouve $e) {
		$msg = $e->getMessage();
		$_SESSION['erreurClient'] = $msg;
		ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
	} catch (ExceptionEmployeExisteDeja $e) {
		$msg = $e->getMessage();
		$_SESSION['erreurExiste'] = $msg;
		ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
	} catch (ExceptionCategorie $e) {
		$msg = $e->getMessage();
		$_SESSION['erreurCat'] = $msg;
		ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
	} catch (ExceptionIdNonTrouveSynthese $e) {
		$msg = $e->getMessage();
		$_SESSION['erreurIdSynthese'] = $msg;
		ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
	} catch (ExceptionClientExiste $e) {
		$msg = $e->getMessage();
		$_SESSION['erreurClientExiste'] = $msg;
		ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
	} catch (ExceptionIdNonTrouveGF $e) {
		$msg = $e->getMessage();
		$_SESSION['erreurIdGF'] = $msg;
		ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
	}
	catch (ExceptionEmploye $e) {
		$msg = $e->getMessage();
		$_SESSION['erreurChercherEmploye'] = $msg;
		ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
	}
	catch (ExceptionTypeInterExiste $e) {
		$msg = $e->getMessage();
		$_SESSION['erreurTypeInterExiste'] = $msg;
		ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
	}
	catch (ExceptionMontantNegatif $e) {
		$msg = $e->getMessage();
		$_SESSION['TypesDIntervention']=ctlChercherTypesIntervention();
		$_SESSION['erreurMontantNegatif'] = $msg;
		ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
	}
	catch (ExceptionFormation $e) {
		$msg = $e->getMessage();
		$_SESSION['erreurFormation'] = $msg;
		ctlAfficherPageCorrespondante($_SESSION['empl']->login, $_SESSION['empl']->motDePasse);
	}