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
                "suggestions" => [
                    "Wat is Technolab?",
                    "Hoe werkt dit?",
                    "Wat kan ik vragen?"
                ]
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
                    "blij",
                    "gevoelens"
                ],
                "answer" => "Ik luister. Gevoelens zijn belangrijk. Wil je me meer vertellen?",
                "suggestions" => [
                    "Hoe kan ik beter worden?",
                    "Wat zijn tips?",
                    "Kan ik erover praten?"
                ]
            ],

            "onaardig" => [
                "keywords" => [
                    "homo",
                    "bitch",
                    "niet leuk",
                    "raggen",
                    "albi",
                    "schelden",
                    "grof"
                ],
                "answer" => "Dat is niet zo aardig 😔",
                "suggestions" => [
                    "Sorry, ik bedoelde het niet",
                    "Wat zijn de regels?",
                    "Hoe beter gedragen?"
                ]
            ],

            "hadj" => [
                "keywords" => [
                    "goat",
                    "anis",
                    "hadj",
                    "moussa",
                    "greatest",
                    "algerijnse"
                ],
                "answer" => "Anissssss🐐",
                "suggestions" => [
                    "Wie is anis hadj?",
                    "Wat is een goat?",
                    "Groet de hadj goat!"
                ],
                "sub_topics" => [
                    "wie" => [
                        "keywords" => ["wie is anis hadj", "anis hadj wie", "wie is anis"],
                        "answer" => "Anis Hadj Moussa, de Algerijnse GOAT🐐",
                        "image" => "Images/Anis.png"
                    ],
                ]
            ],

            // ── FIKA ──────────────────────────────────────────────────────────
            "fika" => [
                "keywords" => ["fika"],
                "answer" => "Fika is een gezamenlijke lunch elke woensdag waarbij een team kookt voor iedereen. 🍽️",
                "suggestions" => [
                    "Wie kookt fika?",
                    "Fika budget hoeveel?",
                    "Fika eten wat?"
                ],
                "sub_topics" => [
                    "wanneer" => [
                        "keywords" => ["fika wanneer", "fika dag", "fika woensdag"],
                        "answer" => "Fika is elke woensdag 📅"
                    ],
                    "budget" => [
                        "keywords" => ["fika budget", "fika geld", "fika kosten", "fika hoeveel"],
                        "answer" => "Er mag rond de €50,- voor Fika worden uitgegeven 💶"
                    ],
                    "boodschappen" => [
                        "keywords" => ["fika boodschappen", "fika winkel", "fika plus"],
                        "answer" => "Boodschappen worden meestal bij de Plus beneden gedaan 🛒"
                    ],
                    "eten" => [
                        "keywords" => ["fika eten", "fika koken wat", "fika vegetarisch", "fika veganistisch", "fika alcohol"],
                        "answer" => "We koken veganistisch/vegetarisch 🌱 en consumeren geen alcohol."
                    ],
                    "team" => [
                        "keywords" => ["fika team", "wie kookt fika", "fika kookt"],
                        "answer" => "Na elke Fika wordt door behulp van een rad een nieuw team gekozen, plus nieuwe ingrediënten🍳",
                        "image" => "Images/Fika.png"
                    ],
                    "verhindering" => [
                        "keywords" => ["fika verhinderd", "fika vervanging", "fika geen tijd"],
                        "answer" => "Ben je gekozen maar heb je geen tijd? Zoek dan zelf vervanging! 🔄"
                    ],
                ]
            ],

            // ── BHV ───────────────────────────────────────────────────────────
            "bhv" => [
                "keywords" => ["bhv", "bedrijfshulpverlening"],
                "answer" => "BHV staat voor BedrijfsHulpVerlening. BHV'ers helpen bij noodgevallen. 🚨",
                "suggestions" => [
                    "Bhv wie zijn de bhv'ers?",
                    "Bhv training wanneer?",
                    "Bhv regels wat zijn ze?"
                ],
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["bhv wat", "bhv uitleg", "bhv wat is"],
                        "answer" => "BHV staat voor BedrijfsHulpVerlening. BHV'ers zijn aanwezig om te helpen bij noodgevallen 🚨"
                    ],
                    "wie" => [
                        "keywords" => ["bhv wie", "bhv aanwezig", "bhv board", "bhv knus"],
                        "answer" => "Wie op dit moment BHV'er is, staat op het zwarte board in de knus 🖤",
                        "image" => "Images/BHV.jpg"
                    ],
                    "training" => [
                        "keywords" => ["bhv training", "bhv wanneer training", "bhv opleiding"],
                        "answer" => "Elk jaar volgen medewerkers een BHV training 📚"
                    ],
                    "regels" => [
                        "keywords" => ["bhv regels", "bhv noodgeval", "bhv wat doen"],
                        "answer" => "Lees de regels goed door zodat je weet wat te doen is bij een noodgeval 📋"
                    ],
                ]
            ],

            // ── PASJE / SLEUTEL ───────────────────────────────────────────────
            "pasje" => [
                "keywords" => ["pasje", "liftpas", "badge"],
                "answer" => "Je kunt een pasje aanvragen bij de coördinator medewerkers. 🔑",
                "suggestions" => [
                    "Pasje aanvragen hoe?",
                    "Liftpas wat is het?",
                    "Pasje nachtwerk regels?"
                ],
                "sub_topics" => [
                    "aanvragen" => [
                        "keywords" => ["pasje aanvragen", "pasje krijgen", "hoe pasje", "badge aanvragen"],
                        "answer" => "Je kunt een pasje aanvragen bij de coördinator medewerkers. 🔑",
                        "image" => "Images/Pasje.jpg"
                    ],
                    "liftpas" => [
                        "keywords" => ["liftpas wat", "liftpas toegang", "liftpas openen", "lift pasje"],
                        "answer" => "Sommige medewerkers hebben een liftpas waarmee je de lift kunt gebruiken. Ook hiermee kun je de deur van het gebouw openen."
                    ],
                    "sleutel" => [
                        "keywords" => ["pasje sleutel", "sleutel alarm", "alarm code pasje", "dagco sleutel"],
                        "answer" => "Als dagco krijg je een sleutel van Technolab en wordt uitgelegd hoe het alarm werkt."
                    ],
                    "nacht" => [
                        "keywords" => ["pasje nacht", "pasje weekend", "pasje vakantie", "pasje laat werken", "nachtwerk pasje"],
                        "answer" => "Tussen 23:00-06:00 uur, weekend of vakantie? Informeer Bernard van Da Vinci College. Anders krijgt Technolab een boete! 📞"
                    ],
                ]
            ],

            // ── PENSIOEN ──────────────────────────────────────────────────────
            "pensioenion" => [
                "keywords" => ["pensioen", "brightpensioen"],
                "answer" => "Technolab biedt geen collectieve pensioenregeling, maar BrightPensioen lidmaatschap wordt vergoed! 💰",
                "suggestions" => [
                    "Brightpensioen wat is het?",
                    "Pensioen aanmelden hoe?",
                    "Pensioen kosten wat zijn ze?"
                ],
                "sub_topics" => [
                    "regeling" => [
                        "keywords" => ["pensioen regeling", "pensioen collectief", "collectieve pensioen"],
                        "answer" => "Technolab biedt geen collectieve pensioenregeling 📋"
                    ],
                    "bright" => [
                        "keywords" => ["brightpensioen wat", "bright pensioen uitleg", "pensioen bright"],
                        "answer" => "BrightPensioen lidmaatschap wordt door Technolab vergoed zolang je een arbeidscontract hebt 💙"
                    ],
                    "aanmelden" => [
                        "keywords" => ["pensioen aanmelden", "bright aanmelden", "pensioen formulier"],
                        "answer" => "Ga naar de coördinator medewerker voor het aanmeldformulier 📝"
                    ],
                    "kosten" => [
                        "keywords" => ["pensioen kosten", "pensioen prijs", "pensioen wat kost"],
                        "answer" => "BrightPensioen lidmaatschap wordt volledig vergoed door Technolab 💰"
                    ],
                ]
            ],

            // ── MDT ───────────────────────────────────────────────────────────
            "mdt" => [
                "keywords" => ["mdt", "maatschappelijke diensttijd"],
                "answer" => "MDT staat voor Maatschappelijke DienstTijd. Ben je onder de 30? Dan kan je hiervan profiteren! 📋",
                "suggestions" => [
                    "Mdt voor wie is het?",
                    "Mdt uren hoe registreer ik?",
                    "Mdt extra uitbetalen?"
                ],
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["mdt wat", "mdt uitleg", "mdt wat is"],
                        "answer" => "MDT staat voor Maatschappelijke DienstTijd. Technolab krijgt subsidie voor MDT uren."
                    ],
                    "wie" => [
                        "keywords" => ["mdt wie", "mdt leeftijd", "mdt jonger", "mdt 30", "mdt voor wie"],
                        "answer" => "Ben je jonger dan 30 jaar? Ga naar de MDT coördinator om een formulier in te vullen."
                    ],
                    "uren" => [
                        "keywords" => ["mdt uren", "mdt registreren", "mdt schrijven", "mdt wekelijks"],
                        "answer" => "Registreer wekelijks je gewerkte uren. Uren ophopen of compenseren is niet de bedoeling! ⏱️"
                    ],
                    "extra" => [
                        "keywords" => ["mdt extra", "mdt uitbetalen", "mdt meer werken"],
                        "answer" => "Meer werken en niet kunnen ruilen? Bespreek met je rolverdeler voor uitbetaling 💰"
                    ],
                ]
            ],

            // ── LOONVERKLARING ────────────────────────────────────────────────
            "loon" => [
                "keywords" => ["loon", "loonverklaring", "salaris", "loonstrook"],
                "answer" => "Je loon wordt via een boekhoudingsbureau betaald. Je hebt een loonverklaring én ID kopie nodig. 💳",
                "suggestions" => [
                    "Loon betaling hoe werkt het?",
                    "Loon wat heb ik nodig?",
                    "Loonverklaring sturen naar wie?"
                ],
                "sub_topics" => [
                    "hoe" => [
                        "keywords" => ["loon hoe", "loon betaling", "loon bureau", "salaris hoe"],
                        "answer" => "De betaling van je loon gaat via een boekhoudingsbureau."
                    ],
                    "nodig" => [
                        "keywords" => ["loon nodig", "loon identiteitsbewijs", "loonverklaring nodig", "salaris nodig"],
                        "answer" => "Voor je uitbetaling is een loonverklaring én een kopie van je ID nodig."
                    ],
                    "sturen" => [
                        "keywords" => ["loonverklaring sturen", "loon sturen", "loon email", "loon naar wie"],
                        "answer" => "Stuur je loonverklaring naar boekhouding@technolableiden.nl. Zorg dat het op tijd aankomt!"
                    ],
                ]
            ],

            // ── VOG ───────────────────────────────────────────────────────────
            "vog" => [
                "keywords" => ["vog", "verklaring omtrent gedrag"],
                "answer" => "Een VOG (Verklaring Omtrent Gedrag) is verplicht om in het onderwijs te werken. 🏫",
                "suggestions" => [
                    "Vog aanvragen wie doet dat?",
                    "Vog ontvangen wat dan?",
                    "Vog sturen naar wie?"
                ],
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["vog wat", "vog uitleg", "verklaring omtrent gedrag wat"],
                        "answer" => "Een VOG (Verklaring Omtrent Gedrag) is verplicht om in het onderwijs te werken 🏫"
                    ],
                    "aanvragen" => [
                        "keywords" => ["vog aanvragen", "vog technolab aanvragen", "vog wie vraagt"],
                        "answer" => "De VOG wordt voor jou aangevraagd door Technolab 👍"
                    ],
                    "ontvangen" => [
                        "keywords" => ["vog ontvangen", "vog doorsturen", "vog coördinator", "vog sturen"],
                        "answer" => "Na ontvangst stuur je de VOG door naar de coördinator medewerker 📬"
                    ],
                ]
            ],

            // ── HUISREGELS ────────────────────────────────────────────────────
            "huisregels" => [
                "keywords" => ["huisregels"],
                "answer" => "Zorg dat je rond 8:15 uur binnen bent, dan starten we samen om 8:30 uur. 🕗",
                "suggestions" => [
                    "Huisregels ziek wat doen?",
                    "Huisregels gedrag wat mag niet?",
                    "Huisregels klusjes welke?"
                ],
                "sub_topics" => [
                    "tijd" => [
                        "keywords" => ["huisregels tijd", "huisregels beginnen", "huisregels 8:15", "huisregels 8:30"],
                        "answer" => "Zorg dat je rond 8:15 uur binnen bent, dan starten we samen om 8:30 uur 🕗"
                    ],
                    "ziek" => [
                        "keywords" => ["huisregels ziek", "huisregels verhinderd", "huisregels ziekmelden"],
                        "answer" => "Bel tussen 8:10 en 8:25 uur naar de dagco: 071-5191324 en zeg het je stagebegeleider 📞"
                    ],
                    "gedrag" => [
                        "keywords" => ["huisregels gedrag", "huisregels kauwgom", "huisregels telefoon", "huisregels pet"],
                        "answer" => "Geen kauwgom, telefoon in tas, geen pet in de les, privé blijft privé. 🙅"
                    ],
                    "verlaten" => [
                        "keywords" => ["huisregels verlaten", "huisregels weggaan", "huisregels pand"],
                        "answer" => "Verlaat je het pand? Meld het altijd bij de dagco 🚪"
                    ],
                    "klusjes" => [
                        "keywords" => ["huisregels klusjes", "huisregels opruimen", "huisregels taken"],
                        "answer" => "Klusjes zoals opruimen horen erbij — wij zijn 1 team, 1 taak 💪"
                    ],
                ]
            ],

            // ── URENREGISTRATIE ───────────────────────────────────────────────
            "urenregistratie" => [
                "keywords" => ["urenregistratie", "uren registreren", "uren schrijven"],
                "answer" => "Registreer wekelijks je gewerkte uren. Uren ophopen of compenseren is niet de bedoeling! ⏱️",
                "suggestions" => [
                    "Urenregistratie hoe werkt het?",
                    "Urenregistratie opbouwen mag dat?",
                    "Urenregistratie schema aanpassen?"
                ],
                "sub_topics" => [
                    "hoe" => [
                        "keywords" => ["urenregistratie hoe", "uren registreren hoe", "uren schrijven hoe"],
                        "answer" => "Registreer wekelijks je gewerkte uren. Uren ophopen of compenseren is niet de bedoeling! ⏱️"
                    ],
                    "opbouwen" => [
                        "keywords" => ["urenregistratie opbouwen", "uren compenseren", "uren ophopen"],
                        "answer" => "Uren opbouwen of compenseren is niet de bedoeling ❌ Bespreek meer werken met je rolverdeler."
                    ],
                    "schema" => [
                        "keywords" => ["urenregistratie schema", "uren schema aanpassen", "werkschema aanpassen", "werkschema veranderen"],
                        "answer" => "Wijzigingen in het werkschema worden van tevoren afgesproken met de rolverdeler 📋"
                    ],
                ]
            ],

            // ── TECHNOLABBER ──────────────────────────────────────────────────
            "technolabber" => [
                "keywords" => ["technolabber", "technolab cultuur", "technolab waarden", "technolab gedragscode"],
                "answer" => "De magie van Technolab in stand houden is essentieel! ✨ We verwachten dat je je als echte Technolabber gedraagt.",
                "suggestions" => [
                    "Wat is Technolab?",
                    "Hoe werkt dit?",
                    "Wat kan ik vragen?"
                ]
            ],

            // ── APP GROEP ─────────────────────────────────────────────────────
            "appgroep" => [
                "keywords" => ["appgroep", "signal", "app groep"],
                "answer" => "We gebruiken Signal voor werk gerelateerde dingen. 📱 Geen WhatsApp!",
                "suggestions" => [
                    "Signal ziekmelden hoe?",
                    "Signal aanmelden hoe?",
                    "Signal welke app?"
                ],
                "sub_topics" => [
                    "welke" => [
                        "keywords" => ["signal welke", "signal app", "appgroep welke", "whatsapp signal"],
                        "answer" => "We gebruiken Signal voor werk gerelateerde dingen 📱 Geen WhatsApp voor werkzaken!"
                    ],
                    "ziek" => [
                        "keywords" => ["signal ziekmelden", "appgroep ziek", "signal ziek melden"],
                        "answer" => "Ziekmeldingen moeten ook telefonisch doorgegeven worden aan de dagco. App alleen is niet genoeg! 📞"
                    ],
                    "aanmelden" => [
                        "keywords" => ["signal aanmelden", "appgroep joinen", "signal link", "appgroep aanmelden"],
                        "answer" => "Meld je aan via de link die je van Technolab krijgt 🔗"
                    ],
                ]
            ],

            // ── NOODNUMMER ────────────────────────────────────────────────────
            "noodnummer" => [
                "keywords" => ["noodnummer", "noodcontact", "nood formulier"],
                "answer" => "Vul het noodnummerformulier in zodat Technolab jou in geval van nood kan bereiken. 📋",
                "suggestions" => [
                    "Noodnummer formulier waar?",
                    "Noodnummer wie bellen?",
                    "Noodnummer voor wie?"
                ]
            ],

            // ── E-MAILHANDTEKENING ────────────────────────────────────────────
            "emailhandtekening" => [
                "keywords" => ["emailhandtekening", "email handtekening", "handtekening outlook"],
                "answer" => "Je emailhandtekening kan je aanpassen in Outlook met je Technolab plaatje en links. ✉️",
                "suggestions" => [
                    "Emailhandtekening aanmaken hoe?",
                    "Emailhandtekening plaatje toevoegen?",
                    "Emailhandtekening link toevoegen?"
                ],
                "sub_topics" => [
                    "aanmaken" => [
                        "keywords" => ["emailhandtekening aanmaken", "handtekening maken outlook", "eerste handtekening outlook"],
                        "answer" => "Voor het aanmaken van je eerste emailhandtekening is er een handleiding beschikbaar. Vraag die op bij Technolab 📖"
                    ],
                    "plaatje" => [
                        "keywords" => ["emailhandtekening plaatje", "handtekening afbeelding", "handtekening logo", "plaatje toevoegen handtekening"],
                        "answer" => "Nieuwe plaatjes worden via Teams gedeeld. Kopieer het plaatje, ga in Outlook naar Instellingen → e-mailhandtekening en voeg het toe 🖼️"
                    ],
                    "link" => [
                        "keywords" => ["emailhandtekening link", "handtekening link toevoegen", "klikbare link handtekening"],
                        "answer" => "Klik op de afbeelding → 'Koppeling invoegen' → voeg de passende link toe 🔗"
                    ],
                ]
            ],

            // ── HOLACRATIE / WERKOVERLEG ──────────────────────────────────────
            "holacratie" => [
                "keywords" => ["holacratie", "holacratisch", "werkoverleg"],
                "answer" => "Holacratisch werkoverleg is strak vergaderen volgens vaste regels. Elke cirkel heeft wekelijks zo'n overleg. 🔄",
                "suggestions" => [
                    "Holacratie wat is het?",
                    "Holacratie facilitator wie?",
                    "Holacratie cirkel wat is het?"
                ],
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["holacratie wat", "holacratie uitleg", "holacratisch werken wat"],
                        "answer" => "Holacratisch werkoverleg is strak vergaderen volgens vaste regels. Elke cirkel heeft wekelijks zo'n overleg 📅"
                    ],
                    "facilitator" => [
                        "keywords" => ["holacratie facilitator", "holacratie secretaris", "werkoverleg leider", "holacratie leidt"],
                        "answer" => "De facilitator (gekozen per periode) leidt het overleg. De secretaris zorgt dat taken in de teamsplanner worden vastgelegd ✍️"
                    ],
                    "cirkel" => [
                        "keywords" => ["holacratie cirkel", "cirkel werkoverleg", "cirkel holacratie wat"],
                        "answer" => "Een cirkel is een team binnen Technolab. Elke cirkel heeft een eigen werkoverleg en planner 🔵"
                    ],
                ]
            ],

            // ── PLANNER ───────────────────────────────────────────────────────
            "planner" => [
                "keywords" => ["planner", "team agenda", "teamplanner"],
                "answer" => "Elke cirkel heeft een planner die de agenda beheert en veranderingen afstemmt. 📅",
                "suggestions" => [
                    "Planner hoe werkt het?",
                    "Planner wachtwoord waar?",
                    "Planner teamleden overzicht?"
                ],
                "sub_topics" => [
                    "hoe" => [
                        "keywords" => ["planner hoe", "planner werken", "planner agenda hoe"],
                        "answer" => "Elke cirkel heeft een planner die de agenda beheert en veranderingen afstemmt 📋"
                    ],
                    "wachtwoord" => [
                        "keywords" => ["planner wachtwoord", "planner vergrendeld", "planner toegang"],
                        "answer" => "De agenda is vergrendeld met wachtwoord. Vraag het bij de hoofdplanner in overleg met je rolverdeler 🔒"
                    ],
                    "teamleden" => [
                        "keywords" => ["planner teamleden", "planner team overzicht", "planner werkdagen"],
                        "answer" => "In de agenda zit een tabblad met alle teamleden, stagiairs en hun werkdagen. Zorg dat jij er ook bij staat! 👥"
                    ],
                ]
            ],

            // ── BUDDY / COACHING ──────────────────────────────────────────────
            "coaching" => [
                "keywords" => ["buddy", "coaching", "coach"],
                "answer" => "Elke medewerker zoekt een buddy om leerdoelen te bespreken. Coaches helpen met persoonlijke uitdagingen. 🤝",
                "suggestions" => [
                    "Buddy systeem wat is het?",
                    "Coaching hoe werkt het?",
                    "Coach wie is mijn coach?"
                ],
                "sub_topics" => [
                    "buddy" => [
                        "keywords" => ["buddy systeem", "buddy leerdoel", "buddy technolab", "buddy wat"],
                        "answer" => "Elke medewerker zoekt een buddy binnen Technolab om eigen leerdoelen te bespreken en te evalueren 🎯"
                    ],
                    "coach" => [
                        "keywords" => ["coaching hoe", "coaching afspraken", "coach traject", "coach persoonlijk"],
                        "answer" => "Coaches helpen in een traject van 3-4 afspraken met persoonlijke uitdagingen 💬"
                    ],
                    "wie" => [
                        "keywords" => ["coach wie", "coaching wie", "coach organigram", "coach talentontwikkeling"],
                        "answer" => "Cirkel Talentontwikkeling verzorgt Coaching en trainingen. Zie het organigram voor wie op dit moment coach is 🗂️"
                    ],
                ]
            ],

            // ── VERTROUWENSPERSOON ────────────────────────────────────────────
            "vertrouwenspersoon" => [
                "keywords" => ["vertrouwenspersoon", "vertrouwelijk bespreken"],
                "answer" => "Heb je iets vertrouwelijks te bespreken? Ga naar onze vertrouwenspersoon! 🔒",
                "suggestions" => [
                    "Vertrouwenspersoon wie is het?",
                    "Vertrouwenspersoon wat is vertrouwelijk?",
                    "Vertrouwenspersoon contact hoe?"
                ]
            ],

            // ── BUS RIJDEN ────────────────────────────────────────────────────
            "bus" => [
                "keywords" => ["technolab bus", "bus rijden", "bus reserveren"],
                "answer" => "Heb je een rijbewijs? Dan moet je eerst een proefrit doen. Daarna mag je ermee rijden! 🚐",
                "suggestions" => [
                    "Bus reserveren hoe?",
                    "Bus rijden regels?",
                    "Bus fiets reserveren ook?"
                ],
                "sub_topics" => [
                    "rijden" => [
                        "keywords" => ["bus rijden hoe", "bus proefrit", "bus rijbewijs", "bus mag ik rijden"],
                        "answer" => "Heb je een rijbewijs? Dan moet je eerst een proefrit met de Technolab bus doen. Pas daarna mag je ermee rijden 🚗"
                    ],
                    "reserveren" => [
                        "keywords" => ["bus reserveren", "bus boeken", "bus dagco wiki", "fiets reserveren"],
                        "answer" => "Reserveer via de Dagco Wiki! Dit geldt ook voor fietsen! 📅"
                    ],
                ]
            ],

            // ── BOEKHOUDING / INKOPEN ─────────────────────────────────────────
            "boekhouding" => [
                "keywords" => ["boekhouding", "inkopen", "bonnetje", "declareren", "pinpas technolab"],
                "answer" => "Bij Gamma of Plus koop je met je Technolab pasje. Bonnetje inleveren in de kast in de Groei! 🧾",
                "suggestions" => [
                    "Boekhouding zelf betalen hoe?",
                    "Boekhouding pinpas gebruiken?",
                    "Boekhouding online bestellen?"
                ],
                "sub_topics" => [
                    "gamma_plus" => [
                        "keywords" => ["boekhouding gamma", "boekhouding plus", "gamma pasje", "plus pasje"],
                        "answer" => "Bij de Gamma of Plus koop je met je Technolab pasje 🪪 Neem bij de Gamma ook de Gamma-pas mee! Bonnetje in kast in de Groei!"
                    ],
                    "voorschieten" => [
                        "keywords" => ["boekhouding zelf betalen", "boekhouding voorschieten", "boekhouding terugkrijgen", "declareren hoe"],
                        "answer" => "Stuur foto van bonnetje + rekeninggegevens naar boekhouding@technolableiden.nl ✉️ Vraag altijd akkoord van producteigenaar!"
                    ],
                    "pinpas" => [
                        "keywords" => ["boekhouding pinpas", "pinpas technolab gebruiken", "pinpas code"],
                        "answer" => "Foto van bonnetje naar boekhouding en origineel in kast in Groei 🧾 Vraag waar pinpas en code zijn!"
                    ],
                    "online" => [
                        "keywords" => ["boekhouding online", "online bestellen boekhouding", "internet aankoop boekhouding"],
                        "answer" => "Stuur op tijd een link naar boekhouding — liefst met akkoord van producteigenaar 🛒"
                    ],
                    "overig" => [
                        "keywords" => ["boekhouding parkeren", "boekhouding bus wassen", "boekhouding geen bonnetje", "overige kosten boekhouding"],
                        "answer" => "Andere uitgaven zonder bonnetje? Bespreek met boekhouding, we vinden samen een oplossing 🤝"
                    ],
                    "voorraad" => [
                        "keywords" => ["boekhouding voorraad", "voorraad op boekhouding", "toiletpapier boekhouding", "koffie thee voorraad"],
                        "answer" => "Voorraad op raakt? Laat het boekhouding/inkoop weten! 📦"
                    ],
                ]
            ],

            // ── PAPIER HERGEBRUIKEN ───────────────────────────────────────────
            "papier" => [
                "keywords" => ["papier hergebruiken", "papier recyclen", "papier brein", "papier dubbelzijdig"],
                "answer" => "Op Technolab hergebruiken we papier! ♻️ Sorteer papier in het brein: dubbelzijdig of engelszijdig bedrukt.",
                "suggestions" => [
                    "Papier brein hoe sorteer ik?",
                    "Papier brein wat is het?",
                    "Papier duurzaam hoe?"
                ]
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
        $keywordWords = explode(" ", $keyword);

        // For multi-word keywords, require ALL words to approximately match
        if (count($keywordWords) > 1) {
            $matchedWords = 0;
            foreach ($keywordWords as $kw) {
                foreach ($words as $word) {
                    if (abs(strlen($word) - strlen($kw)) > 2) {
                        continue;
                    }
                    $distance = levenshtein($word, $kw);
                    $maxLength = max(strlen($word), strlen($kw));
                    if ($maxLength === 0) continue;
                    $similarity = 1 - ($distance / $maxLength);
                    if ($similarity >= 0.82) {
                        $matchedWords++;
                        break;
                    }
                }
            }
            return $matchedWords === count($keywordWords);
        }

        // Single-word keyword: fuzzy match against individual words
        foreach ($words as $word) {
            if (abs(strlen($word) - strlen($keyword)) > 2) {
                continue;
            }
            $distance = levenshtein($word, $keyword);
            $maxLength = max(strlen($word), strlen($keyword));
            if ($maxLength === 0) continue;
            $similarity = 1 - ($distance / $maxLength);
            if ($similarity >= 0.82) {
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
