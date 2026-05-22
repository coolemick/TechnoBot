<?php

session_start();

header("Content-Type: application/json");

class TechnoBot
{

    private array $intents;
    private array $conversationHistory = [];
    private int $messageCount = 0;

    public function __construct()
    {

        // Initialize conversation history from session
        if (!isset($_SESSION["conversation_history"])) {
            $_SESSION["conversation_history"] = [];
        }
        $this->conversationHistory = $_SESSION["conversation_history"];
        $this->messageCount = count($this->conversationHistory);

        $this->intents = [

            "hallo" => [
                "keywords" => [
                    "hallo",
                    "salam",
                    "helo",
                    "hey",
                    "hi",
                    "hii",
                    "yo",
                    "wsg",
                    "wsp",
                    "sup",
                    "goede avond",
                    "goede morgen",
                    "hoe gaat het"
                ],
                "answer" => "Hallo! Fijn je te ontmoeten! Waar kan ik je mee helpen?",
                "suggestions" => []
            ],

            "feeling" => [
                "keywords" => [
                    "verdrietig",
                    "happy",
                    "tired",
                    "boos",
                    "depressed",
                    "excited",
                    "moe",
                    "blij"
                ],
                "answer" => "Ik luister. Gevoelens zijn belangrijk. Wil je me meer vertellen?",
                "suggestions" => []
            ],

            "onaardig" => [
                "keywords" => [
                    "homo",
                    "bitch",
                    "niet leuk",
                    "raggen",
                    "albi"
                ],
                "answer" => "Dat is niet zo aardig",
                "suggestions" => []
            ],

            "hadj" => [
                "keywords" => [
                    "goat",
                    "anis",
                    "hadj",
                    "moussa",
                    "best",
                    "greatest"
                ],
                "answer" => "Anissssss🐐",
                "suggestions" => [
                    "Wie is Anis?",
                ],
                "sub_topics" => [
                    "wie" => [
                        "keywords" => ["Anis?"],
                        "answer" => "Anis Hadj Moussa, de Algerijnse GOAT🐐",
                        "image" => "Images/Anis.png"
                    ],
                ]
            
            ],

            // ── FIKA ──────────────────────────────────────────────────────────
            "fika" => [
                "keywords" => ["niet","budget","fika", "koken", "eten", "lunch", "boodschappen", "kookt"],
                "answer" => "Fika is een gezamenlijke lunch elke woensdag waarbij een team kookt voor iedereen. 🍽️",
                "suggestions" => [
                    "Wie kookt er?",
                    "Hoeveel budget is er?",
                    "Wat koken we?"
                ],
                "sub_topics" => [
                    "wanneer" => [
                        "keywords" => ["wanneer", "welke dag", "dag", "woensdag"],
                        "answer" => "Fika is elke woensdag 📅"
                    ],
                    "budget" => [
                        "keywords" => ["budget", "geld", "euro", "hoeveel", "kosten", "Hoeveel budget is er?"],
                        "answer" => "Er mag rond de €50,- voor Fika worden uitgegeven 💶"
                    ],
                    "boodschappen" => [
                        "keywords" => ["boodschappen", "winkel", "plus", "kopen"],
                        "answer" => "Boodschappen worden meestal bij de Plus beneden gedaan 🛒"
                    ],
                    "eten" => [
                        "keywords" => ["wat eten", "wat koken", "vegetarisch", "veganistisch", "alcohol", "vlees"],
                        "answer" => "We koken veganistisch/vegetarisch 🌱 en consumeren geen alcohol."
                    ],
                    "team" => [
                        "keywords" => ["wie", "kookt", "groep", "gekozen"],
                        "answer" => "Na elke Fika wordt door behulp van een rad een nieuw team gekozen, plus nieuwe ingrediënten🍳",
                        "image" => "Images/Fika.png"
                    ],
                    "verhindering" => [
                        "keywords" => ["niet", "verhinderd", "vervanging", "afmelden"],
                        "answer" => "Ben je gekozen maar heb je geen tijd? Zoek dan zelf vervanging! 🔄"
                    ],
                ]
            ],

            // ── BHV ───────────────────────────────────────────────────────────
            "bhv" => [
                "keywords" => ["training","bhv", "bedrijfshulpverlening", "noodgeval", "veiligheid", "ehbo"],
                "answer" => "BHV staat voor BedrijfsHulpVerlening. BHV'ers helpen bij noodgevallen. 🚨",
                "suggestions" => [
                    "Wie zijn de BHV'ers?",
                    "Wat is BHV?",
                    "Wanneer is training?",
                ],
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["wat", "wat is", "uitleg"],
                        "answer" => "BHV staat voor BedrijfsHulpVerlening. BHV'ers zijn aanwezig om te helpen bij noodgevallen 🚨"
                    ],
                    "wie" => [
                        "keywords" => ["wie", "aanwezig", "board", "knus"],
                        "answer" => "Wie op dit moment BHV'er is, staat op het zwarte board in de knus 🖤",
                         "image" => "Images/BHV.jpg"
                    ],
                    "training" => [
                        "keywords" => ["Wanneer","training"],
                        "answer" => "Elk jaar volgen medewerkers een BHV training 📚"
                    ],
                    "regels" => [
                        "keywords" => ["regels", "noodgeval", "wat doen"],
                        "answer" => "Lees de regels goed door zodat je weet wat te doen is bij een noodgeval 📋"
                    ],
                ]
            ],

            // ── PASJE / SLEUTEL ───────────────────────────────────────────────
            "pasje" => [
                "keywords" => ["pasje", "sleutel", "deur", "toegang", "liftpas", "pas"],
                "answer" => "Je kunt een pasje aanvragen bij de coördinator medewerkers. 🔑",
                "suggestions" => [
                    "Wat is een liftpas?",
                    "Hoe werkt het alarm?",
                    "Nachtwerk melden?"
                ],
                "sub_topics" => [
                    "aanvragen" => [
                        "keywords" => ["aanvragen", "krijgen", "hoe", "coordinator"],
                        "answer" => "Je kunt een pasje aanvragen bij de coördinator medewerkers."
                    ],
                    "liftpas" => [
                        "keywords" => ["liftpas", "lift", "ook openen"],
                        "answer" => "Sommige medewerkers hebben een liftpas. Ook hiermee kun je de deur van het gebouw openen."
                    ],
                    "sleutel" => [
                        "keywords" => ["sleutel", "alarm", "dagco"],
                        "answer" => "Als dagco krijg je een sleutel van Technolab en wordt uitgelegd hoe het alarm werkt."
                    ],
                    "nacht" => [
                        "keywords" => ["nacht", "weekend", "vakantie", "laat", "23", "bernard", "davinci"],
                        "answer" => "Tussen 23:00-06:00 uur, weekend of vakantie? Informeer Bernard van Da Vinci College. Anders krijgt Technolab een boete! 📞"
                    ],
                ]
            ],

            // ── PENSIOEN ──────────────────────────────────────────────────────
            "pensioen" => [
                "keywords" => ["pensioen", "bright", "brightpensioen"],
                "answer" => "Technolab biedt geen collectieve pensioenregeling, maar BrightPensioen lidmaatschap wordt vergoed! 💰",
                "suggestions" => [
                    "Wat is BrightPensioen?",
                    "Hoe meld ik me aan?",
                    "Wat kost het mij?"
                ],
                "sub_topics" => [
                    "regeling" => [
                        "keywords" => ["regeling", "collectief", "heeft technolab"],
                        "answer" => "Technolab biedt geen collectieve pensioenregeling 📋"
                    ],
                    "bright" => [
                        "keywords" => ["bright", "brightpensioen", "wat is"],
                        "answer" => "BrightPensioen lidmaatschap wordt door Technolab vergoed zolang je een arbeidscontract hebt 💙"
                    ],
                    "aanmelden" => [
                        "keywords" => ["aanmelden", "hoe", "formulier", "coordinator"],
                        "answer" => "Ga naar de coördinator medewerker voor het aanmeldformulier 📝"
                    ],
                ]
            ],

            // ── MDT ───────────────────────────────────────────────────────────
            "mdt" => [
                "keywords" => ["mdt", "maatschappelijke diensttijd", "subsidie", "uren"],
                "answer" => "MDT staat voor Maatschappelijke DienstTijd. Ben je onder de 30? Dan kan je hiervan profiteren! 📋",
                "suggestions" => [
                    "Voor wie is MDT?",
                    "Hoe registreer ik uren?",
                    "Kan ik extra betaald krijgen?"
                ],
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["wat", "uitleg", "wat is"],
                        "answer" => "MDT staat voor Maatschappelijke DienstTijd. Technolab krijgt subsidie voor MDT uren."
                    ],
                    "wie" => [
                        "keywords" => ["wie", "leeftijd", "jonger", "30"],
                        "answer" => "Ben je jonger dan 30 jaar? Ga naar de MDT coördinator om een formulier in te vullen."
                    ],
                    "uren" => [
                        "keywords" => ["uren", "registreren", "schrijven", "wekelijks"],
                        "answer" => "Registreer wekelijks je gewerkte uren. Uren ophopen of compenseren is niet de bedoeling! ⏱️"
                    ],
                    "extra" => [
                        "keywords" => ["extra", "uitbetalen", "meer werken"],
                        "answer" => "Meer werken en niet kunnen ruilen? Bespreek met je rolverdeler voor uitbetaling 💰"
                    ],
                ]
            ],

            // ── LOONVERKLARING ────────────────────────────────────────────────
            "loon" => [
                "keywords" => ["loon", "loonverklaring", "salaris", "betaling", "uitbetaling", "boekhouding"],
                "answer" => "Je loon wordt via een boekhoudingsbureau betaald. Je hebt een loonverklaring én ID kopie nodig. 💳",
                "suggestions" => [
                    "Hoe werkt de betaling?",
                    "Wat heb ik nodig?",
                    "Waar stuur ik alles naar toe?"
                ],
                "sub_topics" => [
                    "hoe" => [
                        "keywords" => ["hoe", "werkt", "boekhoudingsbureau"],
                        "answer" => "De betaling van je loon gaat via een boekhoudingsbureau."
                    ],
                    "nodig" => [
                        "keywords" => ["nodig", "id", "identiteitsbewijs", "loonverklaring"],
                        "answer" => "Voor je uitbetaling is een loonverklaring én een kopie van je ID nodig."
                    ],
                    "sturen" => [
                        "keywords" => ["sturen", "waar", "email", "adres"],
                        "answer" => "Stuur je loonverklaring naar boekhouding@technolableiden.nl. Zorg dat het op tijd aankomt!"
                    ],
                ]
            ],

            // ── VOG ───────────────────────────────────────────────────────────
            "vog" => [
                "keywords" => ["vog", "verklaring omtrent gedrag", "onderwijs"],
                "answer" => "Een VOG (Verklaring Omtrent Gedrag) is verplicht om in het onderwijs te werken. 🏫",
                "suggestions" => [
                    "Wie vraagt het aan?",
                    "Wat doe ik ermee?",
                    "Waar stuur ik het naar toe?"
                ],
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["wat", "wat is"],
                        "answer" => "Een VOG (Verklaring Omtrent Gedrag) is verplicht om in het onderwijs te werken 🏫"
                    ],
                    "aanvragen" => [
                        "keywords" => ["aanvragen", "hoe", "wie vraagt"],
                        "answer" => "De VOG wordt voor jou aangevraagd door Technolab 👍"
                    ],
                    "ontvangen" => [
                        "keywords" => ["ontvangen", "doorsturen", "coordinator"],
                        "answer" => "Na ontvangst stuur je de VOG door naar de coördinator medewerker 📬"
                    ],
                ]
            ],

            // ── HUISREGELS ────────────────────────────────────────────────────
            "huisregels" => [
                "keywords" => ["huisregels", "regels", "gedragsregels", "houding", "telefoon", "naamkaartje"],
                "answer" => "Zorg dat je rond 8:15 uur binnen bent, dan starten we samen om 8:30 uur. 🕗",
                "suggestions" => [
                    "Ziek of verhinderd?",
                    "Wat zijn de gedragsregels?",
                    "Wat zijn mijn taken?"
                ],
                "sub_topics" => [
                    "tijd" => [
                        "keywords" => ["hoe laat", "beginnen", "starten", "tijd", "aanwezig"],
                        "answer" => "Zorg dat je rond 8:15 uur binnen bent, dan starten we samen om 8:30 uur 🕗"
                    ],
                    "ziek" => [
                        "keywords" => ["ziek", "verhinderd", "ns", "trein", "afmelden", "melden"],
                        "answer" => "Bel tussen 8:10 en 8:25 uur naar de dagco: 071-5191324 en zeg het je stagebegeleider 📞"
                    ],
                    "gedrag" => [
                        "keywords" => ["gedrag", "houding", "kauwgom", "pet", "telefoon", "prive"],
                        "answer" => "Geen kauwgom, telefoon in tas, geen pet in de les, privé blijft privé. 🙅"
                    ],
                    "verlaten" => [
                        "keywords" => ["verlaten", "buiten", "weggaan", "melden dagco"],
                        "answer" => "Verlaat je het pand? Meld het altijd bij de dagco 🚪"
                    ],
                    "klusjes" => [
                        "keywords" => ["klusjes", "opruimen", "taken", "team"],
                        "answer" => "Klusjes zoals opruimen horen erbij — wij zijn 1 team, 1 taak 💪"
                    ],
                ]
            ],

            // ── URENREGISTRATIE ───────────────────────────────────────────────
            "urenregistratie" => [
                "keywords" => ["urenregistratie", "uren registreren", "uren schrijven", "werkschema"],
                "answer" => "Registreer wekelijks je gewerkte uren. Uren ophopen of compenseren is niet de bedoeling! ⏱️",
                "suggestions" => [
                    "Mag ik uren opbouwen?",
                    "Hoe pas ik mijn schema aan?",
                    "Wat gebeurt met extra uren?"
                ],
                "sub_topics" => [
                    "hoe" => [
                        "keywords" => ["hoe", "werkt", "schrijven"],
                        "answer" => "Registreer wekelijks je gewerkte uren. Uren ophopen of compenseren is niet de bedoeling! ⏱️"
                    ],
                    "opbouwen" => [
                        "keywords" => ["opbouwen", "compenseren", "ophopen"],
                        "answer" => "Uren opbouwen of compenseren is niet de bedoeling ❌ Bespreek meer werken met je rolverdeler."
                    ],
                    "schema" => [
                        "keywords" => ["schema", "aanpassen", "veranderen", "werkschema"],
                        "answer" => "Wijzigingen in het werkschema worden van tevoren afgesproken met de rolverdeler 📋"
                    ],
                ]
            ],

            // ── TECHNOLABBER ──────────────────────────────────────────────────
            "technolabber" => [
                "keywords" => ["technolabber", "magie", "cultuur", "gedragscode", "wie zijn wij"],
                "answer" => "De magie van Technolab in stand houden is essentieel! ✨ We verwachten dat je je als echte Technolabber gedraagt.",
                "suggestions" => []
            ],

            // ── APP GROEP ─────────────────────────────────────────────────────
            "appgroep" => [
                "keywords" => ["app", "appgroep", "signal", "whatsapp", "groep", "berichten"],
                "answer" => "We gebruiken Signal voor werk gerelateerde dingen. 📱 Geen WhatsApp!",
                "suggestions" => [
                    "Hoe meld ik ziekmeldingen?",
                    "Hoe meld ik me aan?",
                    "Wat is de groep?"
                ],
                "sub_topics" => [
                    "welke" => [
                        "keywords" => ["welke app", "signal", "whatsapp", "wat gebruiken"],
                        "answer" => "We gebruiken Signal voor werk gerelateerde dingen 📱 Geen WhatsApp voor werkzaken!"
                    ],
                    "ziek" => [
                        "keywords" => ["ziekmelden", "ziek melden", "afmelden"],
                        "answer" => "Ziekmeldingen moeten ook telefonisch doorgegeven worden aan de dagco. App alleen is niet genoeg! 📞"
                    ],
                    "aanmelden" => [
                        "keywords" => ["aanmelden", "hoe", "link", "joinen"],
                        "answer" => "Meld je aan via de link die je van Technolab krijgt 🔗"
                    ],
                ]
            ],

            // ── NOODNUMMER ────────────────────────────────────────────────────
            "noodnummer" => [
                "keywords" => ["noodnummer", "nood", "emergency", "contact", "formulier invullen"],
                "answer" => "Vul het noodnummerformulier in zodat Technolab jou in geval van nood kan bereiken. 📋",
                "suggestions" => []
            ],

            // ── E-MAILHANDTEKENING ────────────────────────────────────────────
            "emailhandtekening" => [
                "keywords" => ["emailhandtekening", "handtekening", "email", "outlook", "signature"],
                "answer" => "Je emailhandtekening kan je aanpassen in Outlook met je Technolab plaatje en links. ✉️",
                "suggestions" => [
                    "Hoe maak ik er een aan?",
                    "Hoe voeg ik een plaatje toe?",
                    "Hoe voeg ik een link toe?"
                ],
                "sub_topics" => [
                    "aanmaken" => [
                        "keywords" => ["aanmaken", "eerste keer", "hoe", "nieuw"],
                        "answer" => "Voor het aanmaken van je eerste emailhandtekening is er een handleiding beschikbaar. Vraag die op bij Technolab 📖"
                    ],
                    "plaatje" => [
                        "keywords" => ["plaatje", "afbeelding", "updaten", "nieuw plaatje"],
                        "answer" => "Nieuwe plaatjes worden via Teams gedeeld. Kopieer het plaatje, ga in Outlook naar Instellingen → e-mailhandtekening en voeg het toe 🖼️"
                    ],
                    "link" => [
                        "keywords" => ["link", "koppeling", "klikbaar", "url"],
                        "answer" => "Klik op de afbeelding → 'Koppeling invoegen' → voeg de passende link toe 🔗"
                    ],
                ]
            ],

            // ── HOLACRATIE / WERKOVERLEG ──────────────────────────────────────
            "holacratie" => [
                "keywords" => ["holacratie", "holacratisch", "werkoverleg", "vergadering", "cirkel", "facilitator", "secretaris"],
                "answer" => "Holacratisch werkoverleg is strak vergaderen volgens vaste regels. Elke cirkel heeft wekelijks zo'n overleg. 🔄",
                "suggestions" => [
                    "Wat is holacratie?",
                    "Wie zijn facilitator en secretaris?",
                    "Wat is een cirkel?"
                ],
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["wat", "uitleg", "wat is holacratie"],
                        "answer" => "Holacratisch werkoverleg is strak vergaderen volgens vaste regels. Elke cirkel heeft wekelijks zo'n overleg 📅"
                    ],
                    "facilitator" => [
                        "keywords" => ["facilitator", "secretaris", "wie leidt"],
                        "answer" => "De facilitator (gekozen per periode) leidt het overleg. De secretaris zorgt dat taken in de teamsplanner worden vastgelegd ✍️"
                    ],
                    "cirkel" => [
                        "keywords" => ["cirkel", "wat is een cirkel", "team"],
                        "answer" => "Een cirkel is een team binnen Technolab. Elke cirkel heeft een eigen werkoverleg en planner 🔵"
                    ],
                ]
            ],

            // ── PLANNER ───────────────────────────────────────────────────────
            "planner" => [
                "keywords" => ["planner", "agenda", "wachtwoord", "vergrendeld", "schema"],
                "answer" => "Elke cirkel heeft een planner die de agenda beheert en veranderingen afstemmt. 📅",
                "suggestions" => [
                    "Hoe werkt de planner?",
                    "Waar is het wachtwoord?",
                    "Wie werkt hier?"
                ],
                "sub_topics" => [
                    "hoe" => [
                        "keywords" => ["hoe werkt", "wat is", "planner"],
                        "answer" => "Elke cirkel heeft een planner die de agenda beheert en veranderingen afstemmt 📋"
                    ],
                    "wachtwoord" => [
                        "keywords" => ["wachtwoord", "vergrendeld", "toegang"],
                        "answer" => "De agenda is vergrendeld met wachtwoord. Vraag het bij de hoofdplanner in overleg met je rolverdeler 🔒"
                    ],
                    "teamleden" => [
                        "keywords" => ["teamleden", "overzicht", "wie", "werkdagen"],
                        "answer" => "In de agenda zit een tabblad met alle teamleden, stagiairs en hun werkdagen. Zorg dat jij er ook bij staat! 👥"
                    ],
                ]
            ],

            // ── BUDDY / COACHING ──────────────────────────────────────────────
            "coaching" => [
                "keywords" => ["buddy", "coaching", "coach", "leerdoel", "doelen", "loopbaan"],
                "answer" => "Elke medewerker zoekt een buddy om leerdoelen te bespreken. Coaches helpen met persoonlijke uitdagingen. 🤝",
                "suggestions" => [
                    "Wat is het buddysysteem?",
                    "Hoe werkt coaching?",
                    "Wie is mijn coach?"
                ],
                "sub_topics" => [
                    "buddy" => [
                        "keywords" => ["buddy", "buddysysteem", "leerdoelen"],
                        "answer" => "Elke medewerker zoekt een buddy binnen Technolab om eigen leerdoelen te bespreken en te evalueren 🎯"
                    ],
                    "coach" => [
                        "keywords" => ["coach", "coaching", "traject", "afspraken"],
                        "answer" => "Coaches helpen in een traject van 3-4 afspraken met persoonlijke uitdagingen 💬"
                    ],
                    "wie" => [
                        "keywords" => ["wie", "wie is coach", "organigram", "talentontwikkeling"],
                        "answer" => "Cirkel Talentontwikkeling verzorgt Coaching en trainingen. Zie het organigram voor wie op dit moment coach is 🗂️"
                    ],
                ]
            ],

            // ── VERTROUWENSPERSOON ────────────────────────────────────────────
            "vertrouwenspersoon" => [
                "keywords" => ["vertrouwenspersoon", "vertrouwelijk", "bespreken", "privé probleem"],
                "answer" => "Heb je iets vertrouwelijks te bespreken? Ga naar onze vertrouwenspersoon! 🔒",
                "suggestions" => []
            ],

            // ── BUS RIJDEN ────────────────────────────────────────────────────
            "bus" => [
                "keywords" => ["bus", "technolab bus", "rijbewijs", "proefrit", "auto", "reserveren"],
                "answer" => "Heb je een rijbewijs? Dan moet je eerst een proefrit doen. Daarna mag je ermee rijden! 🚐",
                "suggestions" => [
                    "Hoe reserveer ik?",
                    "Wat zijn de regels?",
                    "Geldt dit ook voor fietsen?"
                ],
                "sub_topics" => [
                    "rijden" => [
                        "keywords" => ["rijden", "rijbewijs", "proefrit", "mag"],
                        "answer" => "Heb je een rijbewijs? Dan moet je eerst een proefrit met de Technolab bus doen. Pas daarna mag je ermee rijden 🚗"
                    ],
                    "reserveren" => [
                        "keywords" => ["reserveren", "reservatie", "dagco wiki", "boeken"],
                        "answer" => "Reserveer via de Dagco Wiki! Dit geldt ook voor fietsen! 📅"
                    ],
                ]
            ],

            // ── BOEKHOUDING / INKOPEN ─────────────────────────────────────────
            "boekhouding" => [
                "keywords" => ["boekhouding", "inkopen", "bonnetje", "pinpas", "gamma", "bestellen", "declareren", "terugbetalen"],
                "answer" => "Bij Gamma of Plus koop je met je Technolab pasje. Bonnetje inleveren in de kast in de Groei! 🧾",
                "suggestions" => [
                    "Hoe betaal ik zelf?",
                    "Hoe gebruik ik de pinpas?",
                    "Hoe bestel ik online?"
                ],
                "sub_topics" => [
                    "gamma_plus" => [
                        "keywords" => ["gamma", "plus", "pasje", "korting"],
                        "answer" => "Bij de Gamma of Plus koop je met je Technolab pasje 🪪 Neem bij de Gamma ook de Gamma-pas mee! Bonnetje in kast in de Groei!"
                    ],
                    "voorschieten" => [
                        "keywords" => ["zelf betalen", "voorschieten", "terugkrijgen", "declareren", "rekeninggegevens"],
                        "answer" => "Stuur foto van bonnetje + rekeninggegevens naar boekhouding@technolableiden.nl ✉️ Vraag altijd akkoord van producteigenaar!"
                    ],
                    "pinpas" => [
                        "keywords" => ["pinpas", "pin", "technolab pas"],
                        "answer" => "Foto van bonnetje naar boekhouding en origineel in kast in Groei 🧾 Vraag waar pinpas en code zijn!"
                    ],
                    "online" => [
                        "keywords" => ["online", "bestellen", "internet", "link"],
                        "answer" => "Stuur op tijd een link naar boekhouding — liefst met akkoord van producteigenaar 🛒"
                    ],
                    "overig" => [
                        "keywords" => ["parkeren", "parkeergeld", "wassen", "bus", "geen bonnetje"],
                        "answer" => "Andere uitgaven zonder bonnetje? Bespreek met boekhouding, we vinden samen een oplossing 🤝"
                    ],
                    "voorraad" => [
                        "keywords" => ["voorraad", "toiletpapier", "koffie", "thee", "papier", "op raakt"],
                        "answer" => "Voorraad op raakt? Laat het boekhouding/inkoop weten! 📦"
                    ],
                ]
            ],

            // ── PAPIER HERGEBRUIKEN ───────────────────────────────────────────
            "papier" => [
                "keywords" => ["papier", "hergebruiken", "brein", "recyclen", "printen"],
                "answer" => "Op Technolab hergebruiken we papier! ♻️ Sorteer papier in het brein: dubbelzijdig of engelszijdig bedrukt.",
                "suggestions" => []
            ],
        ];
    }

    public function respond(string $message): array
    {

        $message = strtolower(trim($message));

        // Normal intent matching
        foreach ($this->intents as $intentName => $intent) {
            foreach ($intent["keywords"] as $keyword) {
                if ($this->matches($message, $keyword)) {
                    return $this->getResponse($intentName, $intent, $message);
                }
            }
        }

        return [
            "reply" => $this->defaultResponse(),
            "buttons" => []
        ];
    }

    private function matches(string $message, string $keyword): bool
    {

        if (str_contains($message, $keyword)) {
            return true;
        }

        $words = explode(" ", $message);

        foreach ($words as $word) {
            if (abs(strlen($word) - strlen($keyword)) > 2) {
                continue;
            }

            $distance = levenshtein($word, $keyword);
            $maxLength = max(strlen($word), strlen($keyword));

            if ($maxLength === 0) {
                continue;
            }

            $similarity = 1 - ($distance / $maxLength);

            if ($similarity >= 0.75) {
                return true;
            }
        }

        return false;
    }

    private function getResponse(
        string $intentName,
        array $intent,
        string $message
    ): array {

        // Intents with sub-topics: check if message targets a specific sub-topic
        if (isset($intent["sub_topics"])) {
            foreach ($intent["sub_topics"] as $subKey => $subTopic) {
                foreach ($subTopic["keywords"] as $keyword) {
                    if ($this->matches($message, $keyword)) {
                        $response = [
                            "reply" => $subTopic["answer"],
                            "buttons" => []
                        ];
                        if (isset($subTopic["image"])) {
                            $response["image"] = $subTopic["image"];
                        }
                        return $response;
                    }
                }
            }
            // No sub-topic matched → return main answer with suggestions as buttons
            $buttons = [];
            $suggestions = $intent["suggestions"] ?? [];
            foreach (array_slice($suggestions, 0, 3) as $suggestion) {
                $buttons[] = [
                    "label" => $suggestion,
                    "value" => $suggestion
                ];
            }
            return [
                "reply" => $intent["answer"],
                "buttons" => $buttons
            ];
        }

        // Intents without sub-topics: return answer with max 3 suggestion buttons
        $buttons = [];
        $suggestions = $intent["suggestions"] ?? [];
        foreach (array_slice($suggestions, 0, 3) as $suggestion) {
            $buttons[] = [
                "label" => $suggestion,
                "value" => $suggestion
            ];
        }
        return [
            "reply" => $intent["answer"],
            "buttons" => $buttons
        ];
    }

    private function defaultResponse(): string
    {

        $responses = [
            "Sorry 😅 Dat snap ik nog niet.",
            "Hmm 🤔 Kun je het anders formuleren?",
            "Ik leer nog 👀",
            "Interessant... vertel meer 😄"
        ];

        return $responses[rand(0, count($responses) - 1)];
    }
}

$message = $_POST["message"] ?? "";

$bot = new TechnoBot();

$response = $bot->respond($message);

echo json_encode($response);
