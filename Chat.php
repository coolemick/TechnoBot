<?php

session_start();

header("Content-Type: application/json");

class TechnoBot {

    private array $intents;

    public function __construct() {

        $this->intents = [

            "hallo" => [
                "keywords" => [
                    "hallo", "salam", "helo", "hey", "hi", "hii",
                    "yo", "wsg", "wsp", "sup", "goede avond",
                    "goede morgen", "hoe gaat het"
                ],
                "responses" => [
                    "hallo" => [
                        "Hallo 👋 Hoe kan ik je helpen?",
                        "Hey 😄 Wat kan ik voor je doen?",
                        "Hi daar!",
                        "Hallo 👋",
                        "Heyyy 😎"
                    ],
                    "hoe gaat het" => [
                        "Met mij gaat het goed! En met jou? 😄",
                        "Alles chill! En met jou? 😎"
                    ],
                    "salam" => [
                        "Salam! Hoe kan ik je helpen?",
                        "Salam! Yooo",
                    ],
                    "wsg" => [
                        "Wsg gang 😎",
                        "Yooo wat is goed 🔥",
                        "Wsg bro 👊"
                    ],
                    "wsp" => [
                        "Wsp 😄",
                        "Alles chill?",
                        "Wsp bro 👊"
                    ],
                    "hey" => [
                        "Hey 👋",
                        "Heey 😄",
                        "Yo!"
                    ]
                ],
                "follow_up" => [
                    "Dat heb je al gezegd",
                    "Nog een keer? 😄",
                    "Haha hallo opnieuw 👋",
                ]
            ],

            "feeling" => [
                "keywords" => [
                    "verdrietig", "happy", "tired", "boos", "depressed",
                    "excited", "moe", "blij"
                ],
                "responses" => [
                    "Ik hoop dat alles goed komt ❤️",
                    "Vertel me meer 😄",
                    "Dat klinkt interessant!",
                    "Ik luister 👀",
                    "Gevoelens zijn belangrijk 🙂"
                ],
                "follow_up" => [
                    "Nog steeds daar mee bezig?",
                    "Ik snap het 😄",
                    "Thanks dat je het deelt 👊"
                ]
            ],

            "hadj" => [
                "keywords" => [
                    "hadj", "moussa", "anis", "goat", "best",
                    "greatest"
                ],
                "responses" => [
                    "Anisssss 🐐",
                ],
                "follow_up" => [
                    "The GOAT 🐐",
                ]
            ],

            // ── FIKA ──────────────────────────────────────────────────────────
            "fika" => [
                "keywords" => ["fika", "koken", "eten", "lunch", "boodschappen"],
                "intro" => "Wat wil je weten over Fika? 🍽️\n- Wanneer is het?\n- Hoeveel budget?\n- Wat koken we?\n- Wat als ik niet kan?",
                "sub_topics" => [
                    "wanneer" => [
                        "keywords" => ["wanneer", "welke dag", "dag"],
                        "response" => "Fika is elke woensdag 📅"
                    ],
                    "budget" => [
                        "keywords" => ["budget", "geld", "euro", "hoeveel", "kosten"],
                        "response" => "Er mag rond de €50,- voor Fika worden uitgegeven 💶"
                    ],
                    "boodschappen" => [
                        "keywords" => ["boodschappen", "winkel", "plus", "kopen"],
                        "response" => "Boodschappen worden meestal bij de Plus beneden gedaan 🛒"
                    ],
                    "eten" => [
                        "keywords" => ["wat eten", "wat koken", "vegetarisch", "veganistisch", "alcohol", "vlees"],
                        "response" => "Op Technolab koken we veganistisch/vegetarisch 🌱 We consumeren geen alcohol, ook geen 0.0 dranken."
                    ],
                    "team" => [
                        "keywords" => ["team", "wie", "wie kookt", "groep"],
                        "response" => "Na elke Fika wordt een nieuw team, nieuwe ingrediënten en een nieuwe keuken gekozen 🍳"
                    ],
                    "verhindering" => [
                        "keywords" => ["niet kunnen", "verhinderd", "vervanging", "afmelden"],
                        "response" => "Ben je gekozen maar heb je geen tijd? Zoek dan zelf vervanging! 🔄"
                    ],
                ]
            ],

            // ── BHV ───────────────────────────────────────────────────────────
            "bhv" => [
                "keywords" => ["bhv", "bedrijfshulpverlening", "noodgeval", "veiligheid", "ehbo"],
                "intro" => "Wat wil je weten over BHV? 🚨\n- Wat is het?\n- Wie zijn de BHV'ers?\n- Wanneer training?",
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["wat", "wat is", "uitleg"],
                        "response" => "BHV staat voor BedrijfsHulpVerlening. BHV'ers zijn aanwezig om te helpen bij noodgevallen 🚨"
                    ],
                    "wie" => [
                        "keywords" => ["wie", "aanwezig", "board", "knus"],
                        "response" => "Wie op dit moment aanwezig is als BHV'er, is te zien op het zwarte board in de knus 🖤"
                    ],
                    "training" => [
                        "keywords" => ["training", "opleiding", "cursus", "wanneer"],
                        "response" => "Elk jaar volgen medewerkers een BHV training 📚"
                    ],
                    "regels" => [
                        "keywords" => ["regels", "noodgeval", "wat doen"],
                        "response" => "Lees de regels goed door zodat je weet wat te doen is bij een noodgeval 📋"
                    ],
                ]
            ],

            // ── PASJE / SLEUTEL ───────────────────────────────────────────────
            "pasje" => [
                "keywords" => ["pasje", "sleutel", "deur", "toegang", "liftpas", "pas"],
                "intro" => "Wat wil je weten over het pasje/sleutel? 🔑\n- Hoe vraag ik een pasje aan?\n- Wat is een liftpas?\n- En de sleutel voor dagco's?",
                "sub_topics" => [
                    "aanvragen" => [
                        "keywords" => ["aanvragen", "krijgen", "hoe", "coordinator"],
                        "response" => "Je kunt een pasje aanvragen bij de coördinator medewerkers 🙋"
                    ],
                    "liftpas" => [
                        "keywords" => ["liftpas", "lift", "ook openen"],
                        "response" => "Sommige medewerkers hebben een liftpas. Ook met de liftpas kun je de deur van het gebouw openen 🛗"
                    ],
                    "sleutel" => [
                        "keywords" => ["sleutel", "alarm", "dagco"],
                        "response" => "Als dagco krijg je een sleutel van Technolab en wordt uitgelegd hoe het alarm werkt 🔐"
                    ],
                    "nacht" => [
                        "keywords" => ["nacht", "weekend", "vakantie", "laat", "23", "bernard", "davinci"],
                        "response" => "Als je tussen 23:00 en 06:00 uur, in het weekend of vakantie in het pand bent, moet Bernard van de Da Vinci College geïnformeerd worden. Anders moet Technolab een boete betalen! ⚠️"
                    ],
                ]
            ],

            // ── PENSIOEN ──────────────────────────────────────────────────────
            "pensioen" => [
                "keywords" => ["pensioen", "bright", "brightpensioen"],
                "intro" => "Wat wil je weten over pensioen? 💰\n- Heeft Technolab een pensioenregeling?\n- Wat is BrightPensioen?\n- Hoe aanmelden?",
                "sub_topics" => [
                    "regeling" => [
                        "keywords" => ["regeling", "collectief", "heeft technolab"],
                        "response" => "Technolab biedt geen collectieve pensioenregeling 📋"
                    ],
                    "bright" => [
                        "keywords" => ["bright", "brightpensioen", "wat is"],
                        "response" => "Bij deelname aan BrightPensioen wordt het lidmaatschap door Technolab vergoed zolang je een arbeidscontract hebt 💙"
                    ],
                    "aanmelden" => [
                        "keywords" => ["aanmelden", "hoe", "formulier", "coordinator"],
                        "response" => "Ga naar de coördinator medewerker voor meer uitleg en het aanmeldformulier 📝"
                    ],
                ]
            ],

            // ── MDT ───────────────────────────────────────────────────────────
            "mdt" => [
                "keywords" => ["mdt", "maatschappelijke diensttijd", "subsidie", "uren"],
                "intro" => "Wat wil je weten over MDT? 📋\n- Wat is MDT?\n- Wie komt in aanmerking?\n- Hoe uren registreren?",
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["wat", "uitleg", "wat is"],
                        "response" => "MDT staat voor Maatschappelijke DienstTijd. Technolab krijgt subsidie voor MDT uren 🏛️"
                    ],
                    "wie" => [
                        "keywords" => ["wie", "leeftijd", "jonger", "30"],
                        "response" => "Ben je jonger dan 30 jaar? Ga dan naar de MDT coördinator om een MDT formulier in te vullen 📝"
                    ],
                    "uren" => [
                        "keywords" => ["uren", "registreren", "schrijven", "wekelijks"],
                        "response" => "Voor MDT moeten we wekelijks onze gewerkte uren bijhouden. Uren ophopen of compenseren is niet de bedoeling ⏱️"
                    ],
                    "extra" => [
                        "keywords" => ["extra", "uitbetalen", "meer werken"],
                        "response" => "Moet je echt meer dagen werken en kun je niet ruilen? Dan kun je in overleg met de rolverdeler extra gewerkte dagen laten uitbetalen 💶"
                    ],
                ]
            ],

            // ── LOONVERKLARING ────────────────────────────────────────────────
            "loon" => [
                "keywords" => ["loon", "loonverklaring", "salaris", "betaling", "uitbetaling", "boekhouding"],
                "intro" => "Wat wil je weten over loon/betaling? 💵\n- Hoe werkt de betaling?\n- Wat heb ik nodig?\n- Naar wie stuur ik het?",
                "sub_topics" => [
                    "hoe" => [
                        "keywords" => ["hoe", "werkt", "boekhoudingsbureau"],
                        "response" => "De betaling van je loon gaat via een boekhoudingsbureau 🏢"
                    ],
                    "nodig" => [
                        "keywords" => ["nodig", "id", "identiteitsbewijs", "loonverklaring"],
                        "response" => "Voor je uitbetaling is een loonverklaring én een kopie van je ID nodig 🪪"
                    ],
                    "sturen" => [
                        "keywords" => ["sturen", "waar", "email", "adres"],
                        "response" => "Stuur je loonverklaring naar boekhouding@technolableiden.nl ✉️ Zorg dat het op tijd aankomt!"
                    ],
                ]
            ],

            // ── VOG ───────────────────────────────────────────────────────────
            "vog" => [
                "keywords" => ["vog", "verklaring omtrent gedrag", "onderwijs"],
                "intro" => "Wat wil je weten over de VOG? 📄\n- Wat is een VOG?\n- Hoe wordt het aangevraagd?\n- Wat doe ik ermee?",
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["wat", "wat is"],
                        "response" => "Een VOG (Verklaring Omtrent Gedrag) is verplicht om in het onderwijs te werken 🏫"
                    ],
                    "aanvragen" => [
                        "keywords" => ["aanvragen", "hoe", "wie vraagt"],
                        "response" => "De VOG wordt voor jou aangevraagd door Technolab 👍"
                    ],
                    "ontvangen" => [
                        "keywords" => ["ontvangen", "doorsturen", "coordinator"],
                        "response" => "Na ontvangst stuur je de VOG door naar de coördinator medewerker 📬"
                    ],
                ]
            ],

            // ── HUISREGELS ────────────────────────────────────────────────────
            "huisregels" => [
                "keywords" => ["huisregels", "regels", "gedragsregels", "houding", "telefoon", "naamkaartje"],
                "intro" => "Wat wil je weten over de huisregels? 📏\n- Hoe laat beginnen we?\n- Ziek of verhinderd melden?\n- Gedragscode?",
                "sub_topics" => [
                    "tijd" => [
                        "keywords" => ["hoe laat", "beginnen", "starten", "tijd", "aanwezig"],
                        "response" => "Zorg dat je rond 8:15 uur binnen bent, dan starten we samen om 8:30 uur 🕗"
                    ],
                    "ziek" => [
                        "keywords" => ["ziek", "verhinderd", "ns", "trein", "afmelden", "melden"],
                        "response" => "Verhinderd, ziek of is de NS niet je vriend? Bel dan tussen 8:10 en 8:25 naar de dagco: 071-5191324 📞 Laat het ook je stagebegeleider weten!"
                    ],
                    "gedrag" => [
                        "keywords" => ["gedrag", "houding", "kauwgom", "pet", "telefoon", "prive"],
                        "response" => "Professionele houding: geen kauwgom, telefoon in je tas tijdens de les, geen pet/capuchon in de les, privé blijft privé 🙅"
                    ],
                    "verlaten" => [
                        "keywords" => ["verlaten", "buiten", "weggaan", "melden dagco"],
                        "response" => "Verlaat je Technolab? Ook als je even naar buiten gaat, meld het altijd bij de dagco 🚪"
                    ],
                    "klusjes" => [
                        "keywords" => ["klusjes", "opruimen", "taken", "team"],
                        "response" => "Soms moeten er klusjes worden gedaan zoals opruimen, iets halen etc. Dit hoort er allemaal bij — wij zijn 1 team, 1 taak 💪"
                    ],
                ]
            ],

            // ── URENREGISTRATIE ───────────────────────────────────────────────
            "urenregistratie" => [
                "keywords" => ["urenregistratie", "uren registreren", "uren schrijven", "werkschema"],
                "intro" => "Wat wil je weten over urenregistratie? ⏱️\n- Hoe werkt het?\n- Mag ik uren opbouwen?\n- Werkschema aanpassen?",
                "sub_topics" => [
                    "hoe" => [
                        "keywords" => ["hoe", "werkt", "schrijven"],
                        "response" => "Voor MDT schrijven we wekelijks onze gewerkte uren. Het is niet de bedoeling dat uren worden opgebouwd of gecompenseerd ⏱️"
                    ],
                    "opbouwen" => [
                        "keywords" => ["opbouwen", "compenseren", "ophopen"],
                        "response" => "Uren opbouwen of compenseren is niet de bedoeling ❌ Moet je echt meer werken? Bespreek dit met je rolverdeler."
                    ],
                    "schema" => [
                        "keywords" => ["schema", "aanpassen", "veranderen", "werkschema"],
                        "response" => "Alle veranderingen in het werkschema worden van tevoren met de rolverdeler afgesproken en goedgekeurd 📋"
                    ],
                ]
            ],

            // ── TECHNOLABBER ──────────────────────────────────────────────────
            "technolabber" => [
                "keywords" => ["technolabber", "magie", "cultuur", "gedragscode", "wie zijn wij"],
                "responses" => [
                    "De magie van Technolab in stand houden is essentieel ✨ We verwachten van alle medewerkers dat ze zich als echte Technolabber gedragen. Bekijk het overzicht voor meer info over wat dat inhoudt!"
                ]
            ],

            // ── APP GROEP ─────────────────────────────────────────────────────
            "appgroep" => [
                "keywords" => ["app", "appgroep", "signal", "whatsapp", "groep", "berichten"],
                "intro" => "Wat wil je weten over de app groep? 📱\n- Welke app gebruiken we?\n- Ziekmeldingen via app?\n- Hoe aanmelden?",
                "sub_topics" => [
                    "welke" => [
                        "keywords" => ["welke app", "signal", "whatsapp", "wat gebruiken"],
                        "response" => "We gebruiken Signal voor werk gerelateerde dingen 📱 Geen WhatsApp voor werkzaken!"
                    ],
                    "ziek" => [
                        "keywords" => ["ziekmelden", "ziek melden", "afmelden"],
                        "response" => "Let op: ziekmeldingen moeten altijd ook telefonisch doorgegeven worden aan de dagco. App alleen is niet genoeg! 📞"
                    ],
                    "aanmelden" => [
                        "keywords" => ["aanmelden", "hoe", "link", "joinen"],
                        "response" => "Meld je aan via de link die je van Technolab krijgt 🔗 Staat die link er al bij jou in de onboarding?"
                    ],
                ]
            ],

            // ── NOODNUMMER ────────────────────────────────────────────────────
            "noodnummer" => [
                "keywords" => ["noodnummer", "nood", "emergency", "contact", "formulier invullen"],
                "responses" => [
                    "Vul het noodnummerformulier in zodat Technolab jou in geval van nood kan bereiken 📋 Vraag naar het formulier bij de coördinator medewerker."
                ]
            ],

            // ── E-MAILHANDTEKENING ────────────────────────────────────────────
            "emailhandtekening" => [
                "keywords" => ["emailhandtekening", "handtekening", "email", "outlook", "signature"],
                "intro" => "Wat wil je weten over de emailhandtekening? ✉️\n- Hoe maak ik er een aan?\n- Hoe update ik het plaatje?\n- Hoe voeg ik een link toe?",
                "sub_topics" => [
                    "aanmaken" => [
                        "keywords" => ["aanmaken", "eerste keer", "hoe", "nieuw"],
                        "response" => "Voor het aanmaken van je eerste emailhandtekening is er een handleiding beschikbaar. Vraag die op bij Technolab 📖"
                    ],
                    "plaatje" => [
                        "keywords" => ["plaatje", "afbeelding", "updaten", "nieuw plaatje"],
                        "response" => "Nieuwe plaatjes worden via Teams gedeeld. Kopieer het plaatje, ga in Outlook naar Instellingen → e-mailhandtekening en voeg de afbeelding toe 🖼️"
                    ],
                    "link" => [
                        "keywords" => ["link", "koppeling", "klikbaar", "url"],
                        "response" => "Klik op de afbeelding → 'Koppeling invoegen' → voeg de passende link toe. Zo kunnen mensen direct naar de Technolab pagina! 🔗"
                    ],
                ]
            ],

            // ── HOLACRATIE / WERKOVERLEG ──────────────────────────────────────
            "holacratie" => [
                "keywords" => ["holacratie", "holacratisch", "werkoverleg", "vergadering", "cirkel", "facilitator", "secretaris"],
                "intro" => "Wat wil je weten over holacratie/werkoverleg? 🔄\n- Wat is holacratisch vergaderen?\n- Wie zijn de facilitator en secretaris?\n- Wat is een cirkel?",
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["wat", "uitleg", "wat is holacratie"],
                        "response" => "Holacratisch werkoverleg is strak vergaderen volgens vaste regels. Elke cirkel heeft wekelijks zo'n overleg 📅"
                    ],
                    "facilitator" => [
                        "keywords" => ["facilitator", "secretaris", "wie leidt"],
                        "response" => "De facilitator (per periode gekozen) leidt het overleg. De secretaris zorgt dat taken worden vastgelegd in de teamsplanner ✍️"
                    ],
                    "cirkel" => [
                        "keywords" => ["cirkel", "wat is een cirkel", "team"],
                        "response" => "Een cirkel is een team binnen Technolab. Elke cirkel heeft een eigen werkoverleg en planner 🔵"
                    ],
                ]
            ],

            // ── PLANNER ───────────────────────────────────────────────────────
            "planner" => [
                "keywords" => ["planner", "agenda", "wachtwoord", "vergrendeld", "schema"],
                "intro" => "Wat wil je weten over de planner/agenda? 📅\n- Hoe werkt de planner?\n- Hoe krijg ik het wachtwoord?\n- Teamleden overzicht?",
                "sub_topics" => [
                    "hoe" => [
                        "keywords" => ["hoe werkt", "wat is", "planner"],
                        "response" => "Elke cirkel heeft een planner die de agenda beheert en veranderingen afstemmt 📋"
                    ],
                    "wachtwoord" => [
                        "keywords" => ["wachtwoord", "vergrendeld", "toegang"],
                        "response" => "De agenda is vergrendeld met een wachtwoord (behalve de verkennerskolom). Vraag het wachtwoord aan bij de hoofdplanner, in overleg met je rolverdeler 🔒"
                    ],
                    "teamleden" => [
                        "keywords" => ["teamleden", "overzicht", "wie", "werkdagen"],
                        "response" => "In de agenda zit een tabblad met alle huidige teamleden, stagiairs en hun werkdagen. Zorg dat jij er ook bij staat! 👥"
                    ],
                ]
            ],

            // ── BUDDY / COACHING ──────────────────────────────────────────────
            "coaching" => [
                "keywords" => ["buddy", "coaching", "coach", "leerdoel", "doelen", "loopbaan"],
                "intro" => "Wat wil je weten over buddy/coaching? 🤝\n- Wat is het buddysysteem?\n- Hoe werkt coaching?\n- Wie is mijn coach?",
                "sub_topics" => [
                    "buddy" => [
                        "keywords" => ["buddy", "buddysysteem", "leerdoelen"],
                        "response" => "Elke medewerker zoekt een buddy binnen Technolab om eigen leerdoelen te bespreken en te evalueren 🎯"
                    ],
                    "coach" => [
                        "keywords" => ["coach", "coaching", "traject", "afspraken"],
                        "response" => "Naast de buddy zijn er coaches die in een traject van 3-4 afspraken persoonlijke uitdagingen kunnen begeleiden 💬"
                    ],
                    "wie" => [
                        "keywords" => ["wie", "wie is coach", "organigram", "talentontwikkeling"],
                        "response" => "Cirkel Talentontwikkeling verzorgt Coaching, Loopbaancoaching en trainingen. In het organigram zie je wie op dit moment coach is 🗂️"
                    ],
                ]
            ],

            // ── VERTROUWENSPERSOON ────────────────────────────────────────────
            "vertrouwenspersoon" => [
                "keywords" => ["vertrouwenspersoon", "vertrouwelijk", "bespreken", "privé probleem"],
                "responses" => [
                    "Heb je iets te bespreken en wil je dit vertrouwelijk houden? Ga dan naar onze vertrouwenspersoon 🔒 Op het organigram is de vertrouwenspersoon herkenbaar aan een oranje cirkel."
                ]
            ],

            // ── BUS RIJDEN ────────────────────────────────────────────────────
            "bus" => [
                "keywords" => ["bus", "technolab bus", "rijbewijs", "proefrit", "auto", "reserveren"],
                "intro" => "Wat wil je weten over de Technolab bus? 🚐\n- Hoe mag ik rijden?\n- Hoe reserveer ik?\n- Geldt dit ook voor fietsen?",
                "sub_topics" => [
                    "rijden" => [
                        "keywords" => ["rijden", "rijbewijs", "proefrit", "mag"],
                        "response" => "Heb je een rijbewijs? Dan moet je eerst een proefrit met de Technolab bus doen. Pas daarna mag je ermee rijden 🚗"
                    ],
                    "reserveren" => [
                        "keywords" => ["reserveren", "reservatie", "dagco wiki", "boeken"],
                        "response" => "Heb je een bus of auto nodig? Reserveer via de Dagco Wiki 📅 Dit geldt ook voor fietsen!"
                    ],
                ]
            ],

            // ── ICT ───────────────────────────────────────────────────────────
            "ict" => [
                "keywords" => ["ict", "laptop", "internet", "teams", "scherm", "windows", "beheer", "ticket"],
                "responses" => [
                    "Problemen of vragen over laptops, Windows, internet, Teams of schermen? Maak een ticket aan bij ICT-beheer 💻"
                ]
            ],

            // ── BOEKHOUDING / INKOPEN ─────────────────────────────────────────
            "boekhouding" => [
                "keywords" => ["boekhouding", "inkopen", "bonnetje", "pinpas", "gamma", "bestellen", "declareren", "terugbetalen"],
                "intro" => "Wat wil je weten over inkopen/boekhouding? 🧾\n- Kopen bij Gamma of Plus?\n- Zelf voorschieten en terugkrijgen?\n- Pinpas gebruiken?\n- Iets online bestellen?",
                "sub_topics" => [
                    "gamma_plus" => [
                        "keywords" => ["gamma", "plus", "pasje", "korting"],
                        "response" => "Bij de Gamma of Plus koop je met je Technolab pasje 🪪 Neem bij de Gamma ook de Gamma-pas mee voor korting en spaarpunten. Bonnetje inleveren in de kast in de Groei!"
                    ],
                    "voorschieten" => [
                        "keywords" => ["zelf betalen", "voorschieten", "terugkrijgen", "declareren", "rekeninggegevens"],
                        "response" => "Betaal je zelf? Stuur een foto van het bonnetje + je rekeninggegevens naar boekhouding@technolableiden.nl ✉️ Vraag ook altijd akkoord bij de producteigenaar. Bij grote uitgaven altijd vooraf akkoord vragen!"
                    ],
                    "pinpas" => [
                        "keywords" => ["pinpas", "pin", "technolab pas"],
                        "response" => "Je kunt de Technolab pinpas gebruiken. Stuur het bonnetje als foto naar boekhouding@technolableiden.nl en leg het origineel in de kast in de Groei 🧾 Vraag waar de pinpas en pincode zijn bij boekhouding of de vloermeester."
                    ],
                    "online" => [
                        "keywords" => ["online", "bestellen", "internet", "link"],
                        "response" => "Wil je online bestellen maar niet voorschieten? Stuur op tijd een link naar boekhouding — liefst al met akkoord van de producteigenaar 🛒"
                    ],
                    "overig" => [
                        "keywords" => ["parkeren", "parkeergeld", "wassen", "bus", "geen bonnetje"],
                        "response" => "Andere uitgaven zonder bonnetje (zoals parkeergeld of wasbeurt bus)? Bespreek dit met boekhouding, dan vinden jullie samen een oplossing 🤝"
                    ],
                    "voorraad" => [
                        "keywords" => ["voorraad", "toiletpapier", "koffie", "thee", "papier", "op raakt"],
                        "response" => "Zie je dat voorraad op raakt (toiletpapier, kopieerpapier, koffie, thee etc.)? Laat het boekhouding/inkoop weten! 📦"
                    ],
                ]
            ],

            // ── PAPIER HERGEBRUIKEN ───────────────────────────────────────────
            "papier" => [
                "keywords" => ["papier", "hergebruiken", "brein", "recyclen", "printen"],
                "responses" => [
                    "Op Technolab hergebruiken we papier ♻️ Papier dat niet meer nodig is, sorteer je in het brein: dubbelzijdig of enkelzijdig bedrukt."
                ]
            ],
        ];
    }

    public function respond(string $message): array {

        $message = strtolower(trim($message));

        // Check session context: are we in a sub-topic flow?
        if (isset($_SESSION["pending_intent"])) {
            $pendingIntent = $_SESSION["pending_intent"];

            if (isset($this->intents[$pendingIntent]["sub_topics"])) {
                foreach ($this->intents[$pendingIntent]["sub_topics"] as $subKey => $subTopic) {
                    foreach ($subTopic["keywords"] as $keyword) {
                        if ($this->matches($message, $keyword)) {
                            unset($_SESSION["pending_intent"]);
                            return [
                                "reply" => $subTopic["response"],
                                "buttons" => []
                            ];
                        }
                    }
                }
            }

            // User said something unrelated; clear context and fall through
            unset($_SESSION["pending_intent"]);
        }

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

    private function matches(string $message, string $keyword): bool {

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

        // Intents with simple response arrays (no sub-topics)
        if (!isset($intent["sub_topics"])) {

            if ($intentName === "hallo") {
                if (str_contains($message, "wsg")) {
                    $responses = $intent["responses"]["wsg"];
                } elseif (str_contains($message, "wsp")) {
                    $responses = $intent["responses"]["wsp"];
                } elseif (str_contains($message, "salam")) {
                    $responses = $intent["responses"]["salam"];
                } elseif (str_contains($message, "hey")) {
                    $responses = $intent["responses"]["hey"];
                } elseif (str_contains($message, "hoe gaat het")) {
                    $responses = $intent["responses"]["hoe gaat het"];
                } else {
                    $responses = $intent["responses"]["hallo"];
                }
            } else {
                $responses = $intent["responses"];
            }

            return [
                "reply" => $responses[rand(0, count($responses) - 1)],
                "buttons" => []
            ];
        }

        // Intents with sub-topics: check if message already targets a sub-topic
        foreach ($intent["sub_topics"] as $subKey => $subTopic) {
            foreach ($subTopic["keywords"] as $keyword) {
                if ($this->matches($message, $keyword)) {
                    return [
                        "reply" => $subTopic["response"],
                        "buttons" => []
                    ];
                }
            }
        }

        // No sub-topic matched → show intro menu and store context in session
        $_SESSION["pending_intent"] = $intentName;
        
        // Build buttons from sub-topics
        $buttons = [];
        foreach ($intent["sub_topics"] as $subKey => $subTopic) {
            $buttons[] = [
                "label" => $subTopic["keywords"][0],
                "value" => $subTopic["keywords"][0]
            ];
        }
        
        return [
            "reply" => $intent["intro"],
            "buttons" => $buttons
        ];
    }

    private function defaultResponse(): string {

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