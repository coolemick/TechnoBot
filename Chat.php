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
                    "best",
                    "greatest",
                    "algerijnse"
                ],
                "answer" => "Anissssss🐐",
                "suggestions" => [
                    "Wie is Anis?",
                    "Wat is een GOAT?",
                    "Groet de GOAT!"
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
                "keywords" => ["fika", "koken", "eten", "lunch", "boodschappen", "kookt", "woensdag", "gezamenlijk", "lunch team"],
                "answer" => "Fika is een gezamenlijke lunch elke woensdag waarbij een team kookt voor iedereen. 🍽️",
                "suggestions" => [
                    "Wie kookt er?",
                    "Hoeveel budget is er?",
                    "Wat koken we?"
                ],
                "sub_topics" => [
                    "wanneer" => [
                        "keywords" => ["fika dag", "fika wanneer", "woensdag"],
                        "answer" => "Fika is elke woensdag 📅"
                    ],
                    "budget" => [
                        "keywords" => ["fika budget", "fika geld", "fika kosten"],
                        "answer" => "Er mag rond de €50,- voor Fika worden uitgegeven 💶"
                    ],
                    "boodschappen" => [
                        "keywords" => ["fika boodschappen", "boodschappen plus", "fika winkel"],
                        "answer" => "Boodschappen worden meestal bij de Plus beneden gedaan 🛒"
                    ],
                    "eten" => [
                        "keywords" => ["fika eten", "wat koken", "vegetarisch", "veganistisch", "alcohol"],
                        "answer" => "We koken veganistisch/vegetarisch 🌱 en consumeren geen alcohol."
                    ],
                    "team" => [
                        "keywords" => ["fika team", "fika kookt", "wie kookt fika"],
                        "answer" => "Na elke Fika wordt door behulp van een rad een nieuw team gekozen, plus nieuwe ingrediënten🍳",
                        "image" => "Images/Fika.png"
                    ],
                    "verhindering" => [
                        "keywords" => ["fika verhinderd", "fika vervanging", "geen tijd fika"],
                        "answer" => "Ben je gekozen maar heb je geen tijd? Zoek dan zelf vervanging! 🔄"
                    ],
                ]
            ],

            // ── BHV ───────────────────────────────────────────────────────────
            "bhv" => [
                "keywords" => ["bhv", "bedrijfshulpverlening", "noodgeval", "veiligheid", "ehbo", "training"],
                "answer" => "BHV staat voor BedrijfsHulpVerlening. BHV'ers helpen bij noodgevallen. 🚨",
                "suggestions" => [
                    "Wie zijn de BHV'ers?",
                    "Hoe krijg ik training?",
                    "Wat zijn de regels?"
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
                "keywords" => ["pasje", "sleutel", "deur", "toegang", "liftpas", "badge"],
                "answer" => "Je kunt een pasje aanvragen bij de coördinator medewerkers. 🔑",
                "suggestions" => [
                    "Hoe krijg ik een pasje?",
                    "Wat is de liftpas?",
                    "Nachtwerk vragen?"
                ],
                "sub_topics" => [
                    "aanvragen" => [
                        "keywords" => ["pasje aanvragen", "pasje krijgen", "hoe pasje", "badge krijgen"],
                        "answer" => "Je kunt een pasje aanvragen bij de coördinator medewerkers. 🔑",
                        "image" => "Images/Pasje.jpg"
                    ],
                    "liftpas" => [
                        "keywords" => ["liftpas", "lift toegang", "lift openen"],
                        "answer" => "Sommige medewerkers hebben een liftpas waarmee je de lift kunt gebruiken. Ook hiermee kun je de deur van het gebouw openen."
                    ],
                    "sleutel" => [
                        "keywords" => ["sleutel", "alarm code", "alarm instellingen"],
                        "answer" => "Als dagco krijg je een sleutel van Technolab en wordt uitgelegd hoe het alarm werkt."
                    ],
                    "nacht" => [
                        "keywords" => ["nacht werk", "weekend werk", "vakantie werk", "laat werken"],
                        "answer" => "Tussen 23:00-06:00 uur, weekend of vakantie? Informeer Bernard van Da Vinci College. Anders krijgt Technolab een boete! 📞"
                    ],
                ]
            ],

            // ── PENSIOEN ──────────────────────────────────────────────────────
            "pensioen" => [
                "keywords" => ["pensioen", "bright", "brightpensioen", "pensioenregeling"],
                "answer" => "Technolab biedt geen collectieve pensioenregeling, maar BrightPensioen lidmaatschap wordt vergoed! 💰",
                "suggestions" => [
                    "Wat is BrightPensioen?",
                    "Hoe meld ik me aan?",
                    "Wat zijn de kosten?"
                ],
                "sub_topics" => [
                    "regeling" => [
                        "keywords" => ["pensioen regeling", "collectieve pensioen"],
                        "answer" => "Technolab biedt geen collectieve pensioenregeling 📋"
                    ],
                    "bright" => [
                        "keywords" => ["brightpensioen", "bright pensioen"],
                        "answer" => "BrightPensioen lidmaatschap wordt door Technolab vergoed zolang je een arbeidscontract hebt 💙"
                    ],
                    "aanmelden" => [
                        "keywords" => ["pensioen aanmelden", "bright aanmelden", "pensioen formulier"],
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
                "keywords" => ["loon", "loonverklaring", "salaris", "uitbetaling", "betaling", "loonstrook"],
                "answer" => "Je loon wordt via een boekhoudingsbureau betaald. Je hebt een loonverklaring én ID kopie nodig. 💳",
                "suggestions" => [
                    "Hoe werkt de betaling?",
                    "Wat heb ik nodig?",
                    "Waar stuur ik alles naar toe?"
                ],
                "sub_topics" => [
                    "hoe" => [
                        "keywords" => ["loon betaling", "betaling werkt", "loon bureau"],
                        "answer" => "De betaling van je loon gaat via een boekhoudingsbureau."
                    ],
                    "nodig" => [
                        "keywords" => ["loon nodig", "identiteitsbewijs nodig", "loonverklaring nodig"],
                        "answer" => "Voor je uitbetaling is een loonverklaring én een kopie van je ID nodig."
                    ],
                    "sturen" => [
                        "keywords" => ["loonverklaring sturen", "loon sturen", "loon email"],
                        "answer" => "Stuur je loonverklaring naar boekhouding@technolableiden.nl. Zorg dat het op tijd aankomt!"
                    ],
                ]
            ],

            // ── VOG ───────────────────────────────────────────────────────────
            "vog" => [
                "keywords" => ["vog", "verklaring omtrent gedrag", "vog onderwijs"],
                "answer" => "Een VOG (Verklaring Omtrent Gedrag) is verplicht om in het onderwijs te werken. 🏫",
                "suggestions" => [
                    "Wie vraagt het aan?",
                    "Wat doe ik ermee?",
                    "Waar stuur ik het naar toe?"
                ],
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["vog", "verklaring omtrent"],
                        "answer" => "Een VOG (Verklaring Omtrent Gedrag) is verplicht om in het onderwijs te werken 🏫"
                    ],
                    "aanvragen" => [
                        "keywords" => ["vog aanvragen", "vog technolab"],
                        "answer" => "De VOG wordt voor jou aangevraagd door Technolab 👍"
                    ],
                    "ontvangen" => [
                        "keywords" => ["vog doorsturen", "vog ontvangen", "vog coördinator"],
                        "answer" => "Na ontvangst stuur je de VOG door naar de coördinator medewerker 📬"
                    ],
                ]
            ],

            // ── HUISREGELS ────────────────────────────────────────────────────
            "huisregels" => [
                "keywords" => ["huisregels", "gedragsregels", "gedrag", "houding", "telefoon", "naamkaartje", "8:15", "8:30"],
                "answer" => "Zorg dat je rond 8:15 uur binnen bent, dan starten we samen om 8:30 uur. 🕗",
                "suggestions" => [
                    "Ziek of verhinderd?",
                    "Wat zijn de gedragsregels?",
                    "Wat zijn mijn taken?"
                ],
                "sub_topics" => [
                    "tijd" => [
                        "keywords" => ["hoe laat begin", "8:15", "8:30", "beginnen huisregels"],
                        "answer" => "Zorg dat je rond 8:15 uur binnen bent, dan starten we samen om 8:30 uur 🕗"
                    ],
                    "ziek" => [
                        "keywords" => ["huisregels ziek", "huisregels verhinderd", "ziekmelden", "ns trein"],
                        "answer" => "Bel tussen 8:10 en 8:25 uur naar de dagco: 071-5191324 en zeg het je stagebegeleider 📞"
                    ],
                    "gedrag" => [
                        "keywords" => ["gedrag huisregels", "kauwgom", "pet", "telefoon les", "privacy"],
                        "answer" => "Geen kauwgom, telefoon in tas, geen pet in de les, privé blijft privé. 🙅"
                    ],
                    "verlaten" => [
                        "keywords" => ["huisregels verlaten", "pand verlaten", "weggaan", "melden dagco"],
                        "answer" => "Verlaat je het pand? Meld het altijd bij de dagco 🚪"
                    ],
                    "klusjes" => [
                        "keywords" => ["huisregels klusjes", "klusjes opruimen", "taken", "team opruimen"],
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
                "keywords" => ["technolabber", "magie", "cultuur", "gedragscode", "waarden"],
                "answer" => "De magie van Technolab in stand houden is essentieel! ✨ We verwachten dat je je als echte Technolabber gedraagt.",
                "suggestions" => [
                    "Wat is de Technolab cultuur?",
                    "Wat zijn de waarden?",
                    "Hoe hoort ik me te gedragen?"
                ]
            ],

            // ── APP GROEP ─────────────────────────────────────────────────────
            "appgroep" => [
                "keywords" => ["app", "appgroep", "signal", "berichten", "group", "groep"],
                "answer" => "We gebruiken Signal voor werk gerelateerde dingen. 📱 Geen WhatsApp!",
                "suggestions" => [
                    "Hoe ziekmeld ik me?",
                    "Hoe meld ik me aan?",
                    "Wat is Signal?"
                ],
                "sub_topics" => [
                    "welke" => [
                        "keywords" => ["welke app", "signal app", "whatsapp verboden"],
                        "answer" => "We gebruiken Signal voor werk gerelateerde dingen 📱 Geen WhatsApp voor werkzaken!"
                    ],
                    "ziek" => [
                        "keywords" => ["ziekmelden app", "ziek melden signal"],
                        "answer" => "Ziekmeldingen moeten ook telefonisch doorgegeven worden aan de dagco. App alleen is niet genoeg! 📞"
                    ],
                    "aanmelden" => [
                        "keywords" => ["signal aanmelden", "app joinen", "signal link"],
                        "answer" => "Meld je aan via de link die je van Technolab krijgt 🔗"
                    ],
                ]
            ],

            // ── NOODNUMMER ────────────────────────────────────────────────────
            "noodnummer" => [
                "keywords" => ["noodnummer", "nood", "emergency", "contact formulier"],
                "answer" => "Vul het noodnummerformulier in zodat Technolab jou in geval van nood kan bereiken. 📋",
                "suggestions" => [
                    "Waar is het formulier?",
                    "Wie moet ik bellen?",
                    "Voor wie is het?"
                ]
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
                        "keywords" => ["handtekening maken", "eerste handtekening", "nieuw handtekening"],
                        "answer" => "Voor het aanmaken van je eerste emailhandtekening is er een handleiding beschikbaar. Vraag die op bij Technolab 📖"
                    ],
                    "plaatje" => [
                        "keywords" => ["handtekening plaatje", "handtekening afbeelding", "logo toevoegen"],
                        "answer" => "Nieuwe plaatjes worden via Teams gedeeld. Kopieer het plaatje, ga in Outlook naar Instellingen → e-mailhandtekening en voeg het toe 🖼️"
                    ],
                    "link" => [
                        "keywords" => ["handtekening link", "link toevoegen", "klikbare link"],
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
                        "keywords" => ["holacratie uitleg", "holacratisch werken"],
                        "answer" => "Holacratisch werkoverleg is strak vergaderen volgens vaste regels. Elke cirkel heeft wekelijks zo'n overleg 📅"
                    ],
                    "facilitator" => [
                        "keywords" => ["facilitator holacratie", "secretaris holacratie", "leidt overleg"],
                        "answer" => "De facilitator (gekozen per periode) leidt het overleg. De secretaris zorgt dat taken in de teamsplanner worden vastgelegd ✍️"
                    ],
                    "cirkel" => [
                        "keywords" => ["cirkel holacratie", "cirkel team", "wat is cirkel"],
                        "answer" => "Een cirkel is een team binnen Technolab. Elke cirkel heeft een eigen werkoverleg en planner 🔵"
                    ],
                ]
            ],

            // ── PLANNER ───────────────────────────────────────────────────────
            "planner" => [
                "keywords" => ["planner", "agenda", "wachtwoord", "vergrendeld", "agenda planner", "team agenda", "schedulekaart"],
                "answer" => "Elke cirkel heeft een planner die de agenda beheert en veranderingen afstemmt. 📅",
                "suggestions" => [
                    "Hoe werkt de planner?",
                    "Waar is het wachtwoord?",
                    "Wie werkt hier?"
                ],
                "sub_topics" => [
                    "hoe" => [
                        "keywords" => ["planner werken", "planner agenda", "planner cirkel"],
                        "answer" => "Elke cirkel heeft een planner die de agenda beheert en veranderingen afstemmt 📋"
                    ],
                    "wachtwoord" => [
                        "keywords" => ["planner wachtwoord", "planner vergrendeld", "planner toegang"],
                        "answer" => "De agenda is vergrendeld met wachtwoord. Vraag het bij de hoofdplanner in overleg met je rolverdeler 🔒"
                    ],
                    "teamleden" => [
                        "keywords" => ["planner teamleden", "teamleden overzicht", "planner team", "werkdagen"],
                        "answer" => "In de agenda zit een tabblad met alle teamleden, stagiairs en hun werkdagen. Zorg dat jij er ook bij staat! 👥"
                    ],
                ]
            ],

            // ── BUDDY / COACHING ──────────────────────────────────────────────
            "coaching" => [
                "keywords" => ["buddy", "coaching", "coach", "leerdoel", "doelen", "loopbaan", "mentore"],
                "answer" => "Elke medewerker zoekt een buddy om leerdoelen te bespreken. Coaches helpen met persoonlijke uitdagingen. 🤝",
                "suggestions" => [
                    "Wat is het buddysysteem?",
                    "Hoe werkt coaching?",
                    "Wie is mijn coach?"
                ],
                "sub_topics" => [
                    "buddy" => [
                        "keywords" => ["buddy systeem", "buddy leerdoel", "buddy technolab"],
                        "answer" => "Elke medewerker zoekt een buddy binnen Technolab om eigen leerdoelen te bespreken en te evalueren 🎯"
                    ],
                    "coach" => [
                        "keywords" => ["coach traject", "coaching afspraken", "persoonlijke coach"],
                        "answer" => "Coaches helpen in een traject van 3-4 afspraken met persoonlijke uitdagingen 💬"
                    ],
                    "wie" => [
                        "keywords" => ["coach organigram", "coach talentontwikkeling", "wie coach"],
                        "answer" => "Cirkel Talentontwikkeling verzorgt Coaching en trainingen. Zie het organigram voor wie op dit moment coach is 🗂️"
                    ],
                ]
            ],

            // ── VERTROUWENSPERSOON ────────────────────────────────────────────
            "vertrouwenspersoon" => [
                "keywords" => ["vertrouwenspersoon", "vertrouwelijk", "bespreken", "privé", "geheim"],
                "answer" => "Heb je iets vertrouwelijks te bespreken? Ga naar onze vertrouwenspersoon! 🔒",
                "suggestions" => [
                    "Wie is de vertrouwenspersoon?",
                    "Wat is vertrouwelijk?",
                    "Hoe ga ik contact opnemen?"
                ]
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
                "keywords" => ["kopen", "inkopen", "bonnetje", "pinpas", "gamma", "bestellen", "declareren", "terugbetalen", "plus", "aankoop"],
                "answer" => "Bij Gamma of Plus koop je met je Technolab pasje. Bonnetje inleveren in de kast in de Groei! 🧾",
                "suggestions" => [
                    "Hoe betaal ik zelf?",
                    "Hoe gebruik ik de pinpas?",
                    "Hoe bestel ik online?"
                ],
                "sub_topics" => [
                    "gamma_plus" => [
                        "keywords" => ["gamma pasje", "plus pasje", "gamma korting"],
                        "answer" => "Bij de Gamma of Plus koop je met je Technolab pasje 🪪 Neem bij de Gamma ook de Gamma-pas mee! Bonnetje in kast in de Groei!"
                    ],
                    "voorschieten" => [
                        "keywords" => ["zelf betalen", "voorschieten", "terugkrijgen", "rekeninggegevens"],
                        "answer" => "Stuur foto van bonnetje + rekeninggegevens naar boekhouding@technolableiden.nl ✉️ Vraag altijd akkoord van producteigenaar!"
                    ],
                    "pinpas" => [
                        "keywords" => ["pinpas technolab", "pin code", "technolab pas"],
                        "answer" => "Foto van bonnetje naar boekhouding en origineel in kast in Groei 🧾 Vraag waar pinpas en code zijn!"
                    ],
                    "online" => [
                        "keywords" => ["online bestellen", "internet aankoop", "link bestelling"],
                        "answer" => "Stuur op tijd een link naar boekhouding — liefst met akkoord van producteigenaar 🛒"
                    ],
                    "overig" => [
                        "keywords" => ["parkeren", "wassen bus", "geen bonnetje", "overige kosten"],
                        "answer" => "Andere uitgaven zonder bonnetje? Bespreek met boekhouding, we vinden samen een oplossing 🤝"
                    ],
                    "voorraad" => [
                        "keywords" => ["voorraad op", "toiletpapier", "koffie thee", "voorraad melden"],
                        "answer" => "Voorraad op raakt? Laat het boekhouding/inkoop weten! 📦"
                    ],
                ]
            ],

            // ── PAPIER HERGEBRUIKEN ───────────────────────────────────────────
            "papier" => [
                "keywords" => ["papier", "hergebruiken", "recyclen", "brein", "dubbelzijdig", "engelszijdig", "geprint"],
                "answer" => "Op Technolab hergebruiken we papier! ♻️ Sorteer papier in het brein: dubbelzijdig of engelszijdig bedrukt.",
                "suggestions" => [
                    "Hoe sorteer ik papier?",
                    "Wat is het brein?",
                    "Wat is duurzaam?"
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
