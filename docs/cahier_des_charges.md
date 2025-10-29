📑 Cahier de Charges – Version Détaillée

1. Utilisateurs (Who ?)
   ● Admin (1 seul, pas multi-comptes)
   ● Médecin (géré par admin, pas de compte séparé)
   ● Patient (a un compte)
   ● Visiteur (sans compte)
2. Rôles et Actions (What can he do?)
   🔹 Admin
   ● Gérer médecins (ajouter/supprimer/modifier leurs infos, planning).
   ● Gérer patients (consulter fiches, historiques).
   ● Gérer rendez-vous (valider, reprogrammer, annuler).
   ● Gérer urgences de nuit (définir créneaux, nombre max).
   ● Voir inscriptions (adhésion association, formations, urgences).
   ● Compléter fiche de consultation (pathologie, prix, traitement).
   ● Générer document de tarification (PDF imprimable).
   ● Gérer événements association (créer, archiver).
   ● Gérer formations (créer article, vidéo, document).
   ● Valider témoignages patients avant affichage.
   ● Voir statistiques globales (nombre de patients, rendez-vous par médecin, urgences
   traitées…).
   🔹 Patient
   ● Créer compte, se connecter, réinitialiser mot de passe.
   ● Prendre rendez-vous (consultation / traitement direct).
   ● Prendre rendez-vous d’urgence.
   ● Consulter historique de ses rendez-vous.
   ● Annuler ou reprogrammer un rendez-vous.
   ● Accéder à ses documents (PDF, radios, ordonnances).
   ● Recevoir notifications (WhatsApp ou email).
   🔹 Visiteur
   ● Consulter présentation du cabinet.
   ● Consulter présentation association.
   ● Voir liste des formations (pas accès aux documents internes).
3. Données & Structures (Data Structures)
   Patient
   ● ID patient
   ● Nom, Prénom
   ● Téléphone (unique)
   ● Email
   ● Mot de passe (hashé)
   ● Historique rendez-vous [array]
   ● Documents (analyses, radios, ordonnances) [array de fichiers]
   Médecin
   ● ID médecin
   ● Nom, Spécialité
   ● Diplômes, Expérience (texte)
   ● Planning [jours dispo, créneaux horaires]
   ● Nombre de patients suivis (stat)
   Rendez-vous
   ● ID RDV
   ● Patient (FK)
   ● Médecin (FK)
   ● Type (Consultation / Traitement direct / Urgence)
   ● Date/Heure
   ● Statut (En attente / Confirmé / Annulé / Terminé)
   ● Notes admin (pathologie, prix, traitement)
   Association – Adhésion
   ● ID demande
   ● Nom, Prénom, Numéro
   ● Date inscription
   ● Statut (Vu / Non vu)
   Formation
   ● ID formation
   ● Titre
   ● Description
   ● Formateur
   ● Durée
   ● Documents associés (PDF, vidéo, article)
   Événement
   ● ID événement
   ● Titre
   ● Date
   ● Description
   ● Galerie (photos/vidéos)
4. Agents / Droits (Can & Can’t)
   Acteur Peut faire ✅ Ne peut pas ❌
   Admin Tout gérer (patients, médecins, RDV, docs,
   stats)
   Supprimer compte patient sans
   backup
   Médecin (Pas de compte) → infos gérées
   uniquement par Admin
   Se connecter directement
   Patient RDV, consulter docs, annuler/reporter Modifier planning médecins
   Visiteur Voir infos publiques Voir documents internes
5. Workflows & Notifications
   🔹 Prise de RDV Normal
6. Patient remplit formulaire → Statique (données enregistrées)
7. Système envoie notification WhatsApp à Admin → Dynamique
8. Admin voit demande dans dashboard → Dynamique
9. Admin appel le client pour confirmer
10. Admin valide/programme → Dynamique
11. Système envoie confirmation au patient → Dynamique (ou il fait une appel avec le
    client)
    🔹 Consultation & Facturation
12. Patient se présente au cabinet → Physique
13. Médecin effectue consultation → Physique
14. Admin complète fiche post-consultation → Dynamique
    ○ Pathologie diagnostiquée
    ○ Traitements nécessaires (cocher cases)
    ○ Prix par traitement
15. Système génère devis/facture PDF → Dynamique
16. Admin imprime document pour patient → Physique
    🔹 Urgence de Nuit
17. Patient/Visiteur remplit formulaire urgence → Statique
18. Système envoie ALERTE WhatsApp Admin → Dynamique (prioritaire)
19. Admin traite immédiatement → Dynamique
    🔹 Adhésion Association
20. Visiteur remplit formulaire → Statique
21. Système notifie Admin via WhatsApp → Dynamique
22. Admin contacte via WhatsApp → Dynamique
23. Admin change statut adhésion → Dynamique
