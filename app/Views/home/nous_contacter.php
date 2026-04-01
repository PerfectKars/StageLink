<section class="section">
    <div class="container">

        <h1>Nous contacter</h1>
        <p>
            Vous avez des questions ou souhaitez nous envoyer une demande ? Remplissez ce formulaire et nous vous répondrons rapidement.
        </p>

        <form action="[VOTRE_SCRIPT_DE_TRAITEMENT]" method="post">

            <!-- Question 1 : Nom -->
            <label for="name">1. Votre nom :</label><br>
            <input type="text" id="name" name="name" placeholder="Entrez votre nom" required><br><br>

            <!-- Question 2 : E-mail -->
            <label for="email">2. Votre e-mail :</label><br>
            <input type="email" id="email" name="email" placeholder="Entrez votre email" required><br><br>

            <!-- Question 3 : Type de demande -->
            <label for="type">3. Type de demande :</label><br>
            <select id="type" name="type" required>
                <option value="">-- Sélectionnez --</option>
                <option value="stage">Demande de stage</option>
                <option value="entreprise">Entreprise partenaire</option>
                <option value="support">Support technique</option>
                <option value="autre">Autre</option>
            </select><br><br>

            <!-- Question 4 : Sujet ou question -->
            <label for="subject">4. Sujet ou question :</label><br>
            <input type="text" id="subject" name="subject" placeholder="Titre de votre demande" required><br><br>

            <!-- Question 5 : Message -->
            <label for="message">5. Votre message :</label><br>
            <textarea id="message" name="message" rows="6" placeholder="Décrivez votre demande ou votre question" required></textarea><br><br>

            <!-- Consentement RGPD -->
            <input type="checkbox" id="consent" name="consent" required>
            <label for="consent">
                J’accepte que mes données soient utilisées dans le cadre de ma demande, conformément à la 
                <a href="[URL_VOS_MENTIONS_LEGALES]">politique de confidentialité</a>.
            </label><br><br>

            <button type="submit">Envoyer</button>

        </form>

        <h2>Informations de contact</h2><br>
        <p>
            <strong>StageLink</strong><br>
            Société spécialisée dans la mise en relation entre étudiants et entreprises pour des stages.<br>
            Adresse : 19 avenue Forêt de Haye, 54500 Vandoeuvre-lès-Nancy<br>
            Téléphone : 03 12 34 56 78<br>
            E-mail : <a href="mailto:contact@stagelink.fr">contact@stagelink.fr</a><br>
            Directeur de la publication : StageLink Compagny
        </p><br>

        <h2>Hébergement</h2><br>
        <p>
            Le site StageLink est hébergé par : StageLink Compagny<br>
            Adresse : 19 avenue Forêt de Haye, 54500 Vandoeuvre-lès-Nancy<br>
            Téléphone : 03 12 34 56 78<br>
            E-mail : <a href="mailto:contact@stagelink.fr">contact@stagelink.fr</a>
        </p>

    </div>
</section>