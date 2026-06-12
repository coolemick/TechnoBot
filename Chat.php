<?php

session_start();

header("Content-Type: application/json");

class TechnoBot
{

    private array $intents;
    private array $conversationHistory = [];
    private int $messageCount = 0;
    private array $synonyms = [];
    private array $semanticGroups = [];
    private array $suggestionMap = [];

    private const TIE_THRESHOLD = 0.08;

    public function __construct()
    {
        if (!isset($_SESSION["conversation_history"])) {
            $_SESSION["conversation_history"] = [];
        }
        $this->conversationHistory = $_SESSION["conversation_history"];
        $this->messageCount = count($this->conversationHistory);

        $this->initializeSynonyms();
        $this->initializeSemanticGroups();

        $this->intents = [

            "hallo" => [
                "keywords" => [
                    "hallo",
                    "hoi",
                    "salam",
                    "helo",
                    "hey",
                    "hi",
                    "hii",
                    "yo",
                    "wsp",
                    "wsg",
                    "goede avond",
                    "goede morgen",
                    "hoe gaat het"
                ],
                "answer" => "Hallo! Waar kan ik je mee helpen?",
                "suggestions" => [
                    "Wat is Technolab?",
                    "Wat is fika?",
                    "Hoe betaal ik zelf iets voor boekhouding?"
                ]
            ],

            "technolab" => [
                "keywords" => [
                    "technolab",
                    "wat doen jullie",
                    "hoe groot is technolab",
                    "wat voor projecten doen jullie",
                    "wat kan ik vragen"
                ],
                "answer" => "Technolab Leiden is een leerwerkbedrijf met passie voor onderwijs, techniek, wetenschap en talentontwikkeling.",
                "suggestions" => [
                    "Wat is Technolab?",
                    "Hoe groot is Technolab?",
                    "Wat voor projecten doen jullie?"
                ],
                "sub_topics" => [
                    "groot" => [
                        "keywords" => ["technolab groot", "hoeveel scholen technolab", "hoe groot is technolab"],
                        "answer" => "Ruim 36.000 leerlingen, meer dan 50 scholen en circa 100 bedrijven en organisaties doen elk jaar mee aan de lessen en projecten van Technolab! 🏫",
                    ],
                    "wie" => [
                        "keywords" => ["wie is technolab", "wie zijn wij technolab", "wat is technolab"],
                        "answer" => "Bij Technolab verbinden we onderwijs, techniek en talentontwikkeling. Samen met scholen en bedrijven laten we kinderen, jongeren én medewerkers ontdekken: wie ben ik, wat kan ik, wat wil ik? We maken ze enthousiast voor natuur en techniek: de toekomst! 🌟",
                    ],
                    "projecten" => [
                        "keywords" => ["wat doen jullie technolab", "technolab projecten", "wat voor projecten doen jullie"],
                        "answer" => "We organiseren workshops, lessen (zoals TechniekWijs, ToekomstTaal en Toekomstkunde), POP-UP projectweken, beroepsoriëntatietrajecten (Talent & Toekomst), de Willie Wortel Wedstrijd, de Meesterchallenge en Expeditie Leerkracht. Ook bieden we stages en leerwerkplekken voor mbo-, hbo- en wo-studenten! 🚀",
                    ],
                    "hoe_werkt" => [
                        "keywords" => ["hoe werkt technolab intern", "hoe werkt dit technolab"],
                        "answer" => "Bij Technolab leer je door te doen! Leerlingen, studenten en professionals werken samen in een creatieve omgeving. We geven lessen op locatie in Leiden én op scholen (POP-UP). Medewerkers werken in cirkels (holacratie) en nemen verantwoordelijkheid voor hun eigen rol. Elke dag zijn er zo'n 20 stagiairs actief aan het werk! 💡",
                    ],
                    "wat_kan_vragen" => [
                        "keywords" => ["wat kan ik vragen aan technobot", "wat kun je beantwoorden", "waarmee kan je helpen technolab"],
                        "answer" => "Je kunt mij vragen over van alles rondom Technolab! Denk aan: Fika, BHV, pasjes, pensioen, MDT, loon, VOG, huisregels, urenregistratie, holacratie, de dagco, lessen en leskisten, stage lopen, en nog veel meer. Probeer het gewoon! 😄",
                    ],
                ]
            ],

            "oke" => [
                "keywords" => [
                    "sorry ik bedoelde het niet",
                    "ik snap het niet",
                ],
                "answer" => "Oke, geen probleem! Waar kan ik je mee helpen?",
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
                    "blij",
                    "gevoelens"
                ],
                "answer" => "Ik luister. Gevoelens zijn belangrijk.",
                "suggestions" => []
            ],

            "onaardig" => [
                "keywords" => [
                    "dom",
                    "dumb",
                    "stom",
                    "idioot",
                    "sukkel",
                    "lul",
                    "homo",
                    "bitch",
                    "niet leuk",
                    "niet aardig",
                    "schelden",
                    "grof"
                ],
                "answer" => "Dat is niet zo aardig 😔",
                "suggestions" => [
                    "Sorry, ik bedoelde het niet"
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
                    "Wie is Anis Hadj Moussa?",
                ],
                "sub_topics" => [
                    "wie" => [
                        "keywords" => ["wie is anis hadj moussa", "wie is anis hadj", "wie is de hadj goat"],
                        "answer" => "",
                        "image" => "Images/Anissss.gif"
                    ],
                ]
            ],

            "big D" => [
                "keywords" => ["big d", "dayaan"],
                "answer" => "",
                "image" => "Images/DiddyD.jpg"
            ],

            // ── DAGCO ─────────────────────────────────────────────────────────
            "dagco" => [
                "keywords" => ["dagco", "dagcoordinator", "dagcoördinator"],
                "answer" => "De dagcoördinator (dagco) zorgt elke dag dat Technolab op tijd open is en de dag goed verloopt. 🗓️",
                "suggestions" => [
                    "Wat doet de dagco?",
                    "Hoe bereik ik de dagco?",
                    "Wie is de dagco vandaag?"
                ],
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["dagco wat doet", "wat doet de dagco", "dagco taken"],
                        "answer" => "De dagco zorgt dat Technolab op tijd open is, zet koffie en thee klaar, opent de dag in de kring met een energiser en houdt gedurende de dag bij wie het pand verlaat of terugkomt. Ook regel je vervoer via de dagco! 🚐☕",
                    ],
                    "bereiken" => [
                        "keywords" => ["dagco bellen", "hoe bereik ik de dagco", "dagco nummer", "dagco telefoon"],
                        "answer" => "De dagco is bereikbaar op 071-5191324. Bel bij ziekte of verhindering tussen 08:10 en 08:25 uur! 📞",
                    ],
                    "wie" => [
                        "keywords" => ["wie is de dagco vandaag", "dagco vandaag wie"],
                        "answer" => "Elke dag is er iemand uit een ander team dagco. Kijk op de planner of vraag het aan een collega wie het vandaag is! 👀",
                    ],
                    "sleutel" => [
                        "keywords" => ["dagco sleutel", "dagco alarm openen"],
                        "answer" => "Als dagco krijg je een sleutel van Technolab en wordt uitgelegd hoe het alarm werkt 🔑",
                    ],
                ]
            ],

            // ── PROJECTDAG ────────────────────────────────────────────────────
            "projectdag" => [
                "keywords" => [
                    "projectdag",
                    "project dag",
                    "botsende bots",
                    "groene daken",
                    "mens en robot",
                    "ontwerp je attractie",
                    "duurzaam huis",
                    "avontuurlijke architecten",
                    "welke projecten zijn er",
                    "projecten voor leerlingen",
                    "basisschool projecten"
                ],
                "answer" => "Technolab heeft zes projectdagen voor verschillende groepen! Van robotica tot duurzame architectuur — elk project combineert ontwerpen, samenwerken en onderzoek. 🔧🌱",
                "suggestions" => [
                    "Wat is Botsende Bots?",
                    "Wat is Groene Daken?",
                    "Wat is Duurzaam Huis?"
                ],
                "sub_topics" => [
                    "botsende_bots" => [
                        "keywords" => [
                            "botsende bots",
                            "wat is botsende bots",
                            "botsende bots groep 8",
                            "bots programmeren",
                            "robots bouwen groep"
                        ],
                        "answer" => "🤖 **Botsende Bots** (Groep 8)\n\n**Contactpersoon TK:** Julian\n**Opdrachtgever:** Melissa\n\n**Samenvatting:** Leerlingen lossen ontwerpproblemen op, leren de basis van programmeren en werken samen in drietallen.\n\n**Lesdoelen:**\n- Ontwerpproblemen oplossen door creativiteit en doorzettingsvermogen\n- De basis leren van programmeren\n- Samenwerken in drietallen\n- Leren werken met de ontwerpcyclus: testen en verbeteren",
                    ],
                    "groene_daken" => [
                        "keywords" => [
                            "groene daken",
                            "wat is groene daken",
                            "groene daken groep 7",
                            "zonnepaneel project",
                            "groen dak bouwen",
                            "zonneboiler project"
                        ],
                        "answer" => "🌿 **Groene Daken** (Groep 7)\n\n**Contactpersoon TK:** Roos\n**Contactpersoon KIEM:** Alide / Johan (Solar Groep)\n**Opdrachtgever:** Johan / Solar Groep\n\n**Samenvatting:** Leerlingen ontwerpen en maken hun eigen groene dak, maken kennis met installatietechniek, doen onderzoek naar de optimale stand van een zonnepaneel, het beste materiaal voor een zonneboiler en geschikte planten voor een groen dak.\n\n**Lesdoelen:**\n- Ontwerpproblemen oplossen door creativiteit en doorzettingsvermogen\n- Samenwerken in drietallen\n- Leren zelf een praktisch onderzoek te doen",
                    ],
                    "mens_en_robot" => [
                        "keywords" => [
                            "mens en robot",
                            "wat is mens en robot",
                            "mens robot groep 6",
                            "skelet bouwen",
                            "hartfunctie onderzoek"
                        ],
                        "answer" => "🦾 **Mens en Robot** (Groep 6)\n\n**Contactpersoon TK:** Eline / Roos\n\n**Samenvatting:** Leerlingen bouwen een menselijk skelet met aandacht voor vorm en functie, doen onderzoek naar hartfunctie, bewegen en verhoudingen, en bouwen een robot die voor de mens van nut kan zijn.\n\n**Lesdoelen:**\n- Ontwerpproblemen oplossen door creativiteit en doorzettingsvermogen\n- Samenwerken in drietallen\n- Leren zelf een praktisch onderzoek te doen",
                    ],
                    "ontwerp_attractie" => [
                        "keywords" => [
                            "ontwerp je attractie",
                            "wat is ontwerp je attractie",
                            "attractie bouwen groep 5",
                            "pretpark ontwerpen",
                            "attractie schaal programmeren"
                        ],
                        "answer" => "🎢 **Ontwerp je Attractie** (Groep 5)\n\n**Contactpersoon TK:** Robert / Celine\n**Contactpersoon KIEM:** Coen (Joravision)\n**Opdrachtgever:** Coen\n\n**Samenvatting:** Leerlingen ontwerpen en bouwen hun eigen attractie op schaal als onderdeel van een nieuw pretpark. De groepen stemmen met elkaar af om samen een zo gevarieerd mogelijk pretpark te maken.\n\n**Lesdoelen:**\n- Ontwerpproblemen oplossen door creativiteit en doorzettingsvermogen\n- De basis leren van programmeren\n- Samenwerken in drietallen en afstemmen met andere groepen",
                    ],
                    "duurzaam_huis" => [
                        "keywords" => [
                            "duurzaam huis",
                            "wat is duurzaam huis",
                            "duurzaam huis groep 4",
                            "huis isolatie project",
                            "duurzaam bouwen groep"
                        ],
                        "answer" => "🏡 **Duurzaam Huis** (Groep 4)\n\n**Contactpersoon TK:** Jolien / Roos\n\n**Samenvatting:** Leerlingen ontwerpen en bouwen een duurzaam huis, doen onderzoek naar isolatie, elektriciteit en verbruik van apparaten.\n\n**Lesdoelen:**\n- Ontwerpproblemen oplossen door creativiteit en doorzettingsvermogen\n- Samenwerken in viertallen\n- Leren zelf een praktisch onderzoek te doen",
                    ],
                    "avontuurlijke_architecten" => [
                        "keywords" => [
                            "avontuurlijke architecten",
                            "wat is avontuurlijke architecten",
                            "architecten groep 3",
                            "pretpark bouwen groep 3",
                            "bruggen bouwen project"
                        ],
                        "answer" => "🏗️ **Avontuurlijke Architecten** (Groep 3)\n\n**Contactpersoon TK:** Sanne (Leiden)\n**Contactpersoon KIEM:** Alide\n\n**Samenvatting:** Leerlingen ontwerpen en bouwen hun eigen pretpark met verschillende constructiematerialen. De groepen stemmen samen af. Denk aan bruggen, omheiningen, wegen en bewegwijzering!\n\n**Lesdoelen:**\n- Ontwerpproblemen oplossen door creativiteit en doorzettingsvermogen\n- Samenwerken in drietallen en afstemmen met andere groepen",
                    ],
                ]
            ],

            // ── LESKISTEN ─────────────────────────────────────────────────────
            "leskisten" => [
                "keywords" => ["leskisten", "leskist", "blauwe kisten", "blauwe leskist"],
                "answer" => "De blauwe leskisten zijn de herkenbare kisten waarmee Technolab lessen worden gegeven! 📦",
                "suggestions" => [
                    "Wat zitten er in de leskisten?",
                    "Welke lessen horen bij de leskisten?",
                    "Waar worden leskisten gebruikt?"
                ],
                "sub_topics" => [
                    "inhoud" => [
                        "keywords" => ["leskist inhoud", "wat zitten er in de leskisten", "leskist materiaal"],
                        "answer" => "In de blauwe leskisten zit al het materiaal voor een Technolab les: handleidingen, materialen voor experimenten en opdrachten. Alles wat je nodig hebt voor een goede les zit erin! 🔬🔧",
                    ],
                    "lessen" => [
                        "keywords" => ["leskist lessen", "welke lessen horen bij de leskisten"],
                        "answer" => "De leskisten horen bij de lessen van Technolab, zoals TechniekWijs (wetenschap & techniek), ToekomstTaal (programmeren & mediawijsheid) en Toekomstkunde (duurzaamheid & technologie). 📚",
                    ],
                    "gebruik" => [
                        "keywords" => ["leskist gebruik", "leskist school", "waar worden leskisten gebruikt"],
                        "answer" => "Leskisten worden gebruikt bij lessen op Technolab zelf én bij POP-UP lessen op scholen in de regio Leiden. Zo brengen we de Technolab-ervaring direct naar de klas! 🏫",
                    ],
                ]
            ],

            // ── STAGE LOPEN ───────────────────────────────────────────────────
            "stage" => [
                "keywords" => ["stage", "stagiair", "stage lopen", "stageplaats"],
                "answer" => "Bij Technolab Leiden kun je een actieve en afwisselende stage lopen in een creatieve omgeving! 🎓",
                "suggestions" => [
                    "Hoe meld ik me aan voor een stage?",
                    "Wat kan ik doen tijdens mijn stage?",
                    "Voor welke studierichtingen is stage mogelijk?"
                ],
                "sub_topics" => [
                    "aanmelden" => [
                        "keywords" => ["stage aanmelden", "stage inschrijven", "stage aanvragen", "hoe meld ik me aan voor een stage"],
                        "answer" => "Aanmelden kan via het formulier op technolableiden.nl/over-technolab/stage-leiden/. Daarna volg je de stappen: aanmelden → inspiratiemiddag → ontdekdag → match & start! 📝",
                    ],
                    "wat_doen" => [
                        "keywords" => ["stage wat doe je", "stage werkzaamheden", "wat kan ik doen tijdens mijn stage"],
                        "answer" => "Tijdens je stage werk je mee aan inspirerend techniekonderwijs voor kinderen en jongeren. Je werkt in een multidisciplinair team, krijgt ruimte voor eigen ideeën en begeleiding gericht op jouw leerdoelen. Elke dag zijn er zo'n 20 stagiairs actief! 💪",
                    ],
                    "richtingen" => [
                        "keywords" => ["stage richtingen", "stage opleiding", "voor welke studierichtingen is stage mogelijk"],
                        "answer" => "Technolab zoekt stagiairs uit diverse richtingen, zoals Toegepaste Psychologie, HBO-ICT / Innovative Development, Media Vormgeven en MLO. De mix van achtergronden zorgt voor een inspirerende leeromgeving! 🎨💻",
                    ],
                    "contact" => [
                        "keywords" => ["stage contact", "stage email", "stage vragen bellen"],
                        "answer" => "Vragen over je stage? Bel 071-5191324 of mail naar stage@technolableiden.nl. Let op: tijdens schoolvakanties wordt mail minder vaak gelezen 📧",
                    ],
                ]
            ],

            // ── LESSEN ────────────────────────────────────────────────────────
            "lessen" => [
                "keywords" => [
                    "les",
                    "lessen",
                    "lesaanbod",
                    "lesprogramma",
                    "workshop",
                    "workshops",
                    "techniekwijs",
                    "toekomsttaal",
                    "popup",
                    "willie wortel",
                    "pop up les"
                ],
                "answer" => "Technolab biedt inspirerende lessen en workshops voor PO en VO op het gebied van techniek, wetenschap en talentontwikkeling! 📚",
                "suggestions" => [
                    "Wat zijn alle lesprogramma's?",
                    "Wat is TechniekWijs?",
                    "Wat is ToekomstTaal?"
                ],
                "sub_topics" => [
                    "overzicht" => [
                        "keywords" => ["alle lessen technolab", "welke lessen zijn er", "wat zijn de lesprogrammas van technolab"],
                        "answer" => "Technolab heeft drie hoofdleerlijnen: TechniekWijs (wetenschap & techniek), ToekomstTaal (programmeren & mediawijsheid) en Toekomstkunde (duurzaamheid & technologie). Daarnaast zijn er POP-UP projectweken, mini-stages en de Willie Wortel Wedstrijd! 🔭💻🌱",
                    ],
                    "techniekwijs" => [
                        "keywords" => ["techniekwijs les", "wat is techniekwijs", "wetenschap techniek les"],
                        "answer" => "TechniekWijs is de leerlijn voor wetenschap en techniekonderwijs. Met een rijk aanbod aan apparatuur halen we de uitvinder in leerlingen naar boven! Denk aan workshops over stroomcircuits, katrollen, tandwielen en elektriciteit. 🔧⚡",
                    ],
                    "toekomsttaal" => [
                        "keywords" => ["toekomsttaal les", "programmeren les", "mediawijsheid les", "wat is toekomsttaal"],
                        "answer" => "ToekomstTaal is de leerlijn voor programmeren en mediawijsheid. Leerlingen leren hoe digitale technologie werkt, programmeren met Micro:bit en bouwen eigen robots! 🤖📱",
                    ],
                    "toekomstkunde_les" => [
                        "keywords" => ["toekomstkunde les", "duurzaamheid les", "klimaat les", "wat is toekomstkunde les"],
                        "answer" => "Toekomstkunde gaat over duurzaamheid, hernieuwbare energie, biodiversiteit en klimaatactie. Leerlingen werken hands-on aan oplossingen voor duurzaamheidsvraagstukken. 🌿🌍",
                    ],
                    "popup" => [
                        "keywords" => ["popup les technolab", "pop up projectweek", "les op school popup"],
                        "answer" => "Met POP-UP Technolab komen wij naar jouw school! We verzorgen een week vullend programma met activiteiten op school én bijzondere dagdelen bij Technolab of de Hortus. Er is een programmeer/maakweek én een uitvindersweek. Afgesloten met een tentoonstelling! 🎉",
                    ],
                    "willie_wortel" => [
                        "keywords" => ["willie wortel wedstrijd", "uitvinderswedstrijd technolab"],
                        "answer" => "De Willie Wortel Wedstrijd is een uitvinderswedstrijd voor jonge geniën uit de regio Leiden. Ze bedenken creatieve oplossingen voor uiteenlopende problemen. Er is ook een junior versie voor jongere leerlingen! 🏆💡",
                    ],
                ]
            ],

            // ── FIKA ──────────────────────────────────────────────────────────
            "fika" => [
                "keywords" => ["fika"],
                "answer" => "Fika is een gezamenlijke lunch elke woensdag waarbij een team kookt voor iedereen. 🍽️",
                "suggestions" => [
                    "Wie kookt er bij fika?",
                    "Hoeveel is het fika budget?",
                    "Wat eten we bij fika?"
                ],
                "sub_topics" => [
                    "wanneer" => [
                        "keywords" => ["fika wanneer", "fika welke dag"],
                        "answer" => "Fika is elke woensdag 📅"
                    ],
                    "budget" => [
                        "keywords" => ["fika budget", "fika hoeveel geld", "hoeveel is het fika budget"],
                        "answer" => "Er mag rond de €50,- voor Fika worden uitgegeven 💶"
                    ],
                    "boodschappen" => [
                        "keywords" => ["fika boodschappen winkel", "fika plus supermarkt"],
                        "answer" => "Boodschappen worden meestal bij de Plus beneden gedaan 🛒"
                    ],
                    "eten" => [
                        "keywords" => ["fika eten wat", "fika koken wat", "wat eten we bij fika", "fika vegetarisch veganistisch"],
                        "answer" => "We koken veganistisch/vegetarisch 🌱 en consumeren geen alcohol."
                    ],
                    "team" => [
                        "keywords" => ["fika team kookt", "wie kookt er bij fika", "fika rad draaien"],
                        "answer" => "Na elke Fika wordt door behulp van een rad een nieuw team gekozen, plus nieuwe ingrediënten🍳",
                        "image" => "Images/Fika.png"
                    ],
                    "verhindering" => [
                        "keywords" => ["fika verhinderd vervanging", "fika geen tijd vervanging"],
                        "answer" => "Ben je gekozen maar heb je geen tijd? Zoek dan zelf vervanging! 🔄"
                    ],
                ]
            ],

            // ── BHV ───────────────────────────────────────────────────────────
            "bhv" => [
                "keywords" => ["bhv", "bedrijfshulpverlening"],
                "answer" => "BHV staat voor BedrijfsHulpVerlening. BHV'ers helpen bij noodgevallen. 🚨",
                "suggestions" => [
                    "Wie zijn de BHV'ers?",
                    "Wanneer is de BHV training?",
                    "Wat zijn de BHV regels?"
                ],
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["bhv wat betekent", "wat is bhv uitleg", "wat betekent bhv"],
                        "answer" => "BHV staat voor BedrijfsHulpVerlening. BHV'ers zijn aanwezig om te helpen bij noodgevallen 🚨"
                    ],
                    "wie" => [
                        "keywords" => ["bhv wie zijn", "wie zijn de bhvers", "wie zijn de bhv ers", "bhv board knus"],
                        "answer" => "Wie op dit moment BHV'er is, staat op het zwarte board in de knus 🖤",
                        "image" => "Images/bhv.jpg"
                    ],
                    "training" => [
                        "keywords" => ["bhv training wanneer", "wanneer is de bhv training", "bhv cursus opleiding"],
                        "answer" => "Elk jaar volgen medewerkers een BHV training 📚"
                    ],
                    "regels" => [
                        "keywords" => ["bhv regels noodgeval", "wat zijn de bhv regels", "bhv procedure noodgeval"],
                        "answer" => "Lees de regels goed door zodat je weet wat te doen is bij een noodgeval 📋"
                    ],
                ]
            ],

            // ── PASJE / SLEUTEL ───────────────────────────────────────────────
            "pasje" => [
                "keywords" => ["pasje", "liftpas", "badge"],
                "answer" => "Je kunt een pasje aanvragen bij de coördinator medewerkers. 🔑",
                "suggestions" => [
                    "Hoe vraag ik een pasje aan?",
                    "Wat is de liftpas?",
                    "Wat zijn de regels voor nachtwerk met een pasje?"
                ],
                "sub_topics" => [
                    "aanvragen" => [
                        "keywords" => ["pasje aanvragen hoe", "hoe vraag ik een pasje aan", "badge aanvragen"],
                        "answer" => "Je kunt een pasje aanvragen bij de coördinator medewerkers. 🔑",
                        "image" => "Images/Pasje.jpg"
                    ],
                    "liftpas" => [
                        "keywords" => ["liftpas wat is", "liftpas toegang gebouw", "wat is de liftpas"],
                        "answer" => "Sommige medewerkers hebben een liftpas waarmee je de lift kunt gebruiken. Ook hiermee kun je de deur van het gebouw openen."
                    ],
                    "sleutel" => [
                        "keywords" => ["pasje sleutel alarm", "sleutel alarm dagco"],
                        "answer" => "Als dagco krijg je een sleutel van Technolab en wordt uitgelegd hoe het alarm werkt."
                    ],
                    "nacht" => [
                        "keywords" => ["pasje nacht weekend vakantie", "nachtwerk pasje regels", "wat zijn de regels voor nachtwerk met een pasje"],
                        "answer" => "Tussen 23:00-06:00 uur, weekend of vakantie? Informeer Bernard van Da Vinci College. Anders krijgt Technolab een boete! 📞"
                    ],
                ]
            ],

            // ── PENSIOEN ──────────────────────────────────────────────────────
            "pensioen" => [
                "keywords" => ["pensioen", "brightpensioen"],
                "answer" => "Technolab biedt geen collectieve pensioenregeling, maar BrightPensioen lidmaatschap wordt vergoed! 💰",
                "suggestions" => [
                    "Wat is BrightPensioen?",
                    "Hoe meld ik me aan voor pensioen?",
                    "Wat kost het pensioen?"
                ],
                "sub_topics" => [
                    "regeling" => [
                        "keywords" => ["pensioen collectief regeling"],
                        "answer" => "Technolab biedt geen collectieve pensioenregeling 📋"
                    ],
                    "bright" => [
                        "keywords" => ["brightpensioen wat is", "bright pensioen uitleg", "wat is brightpensioen"],
                        "answer" => "BrightPensioen lidmaatschap wordt door Technolab vergoed zolang je een arbeidscontract hebt 💙"
                    ],
                    "aanmelden" => [
                        "keywords" => ["pensioen aanmelden hoe", "hoe meld ik me aan voor pensioen", "bright pensioen formulier"],
                        "answer" => "Ga naar de coördinator medewerker voor het aanmeldformulier 📝"
                    ],
                    "kosten" => [
                        "keywords" => ["pensioen kosten prijs", "wat kost het pensioen"],
                        "answer" => "BrightPensioen lidmaatschap wordt volledig vergoed door Technolab 💰"
                    ],
                ]
            ],

            // ── MDT ───────────────────────────────────────────────────────────
            "mdt" => [
                "keywords" => ["mdt", "maatschappelijke diensttijd"],
                "answer" => "MDT staat voor Maatschappelijke DienstTijd. Ben je onder de 30? Dan kan je hiervan profiteren! 📋",
                "suggestions" => [
                    "Voor wie is MDT?",
                    "Hoe registreer ik mijn MDT uren?",
                ],
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["mdt wat is uitleg"],
                        "answer" => "MDT staat voor Maatschappelijke DienstTijd. Technolab krijgt subsidie voor MDT uren."
                    ],
                    "wie" => [
                        "keywords" => ["mdt voor wie leeftijd", "mdt jonger dan 30", "voor wie is mdt"],
                        "answer" => "Ben je jonger dan 30 jaar? Ga naar de MDT coördinator om een formulier in te vullen."
                    ],
                    "uren" => [
                        "keywords" => ["mdt uren registreren", "hoe registreer ik mijn mdt uren", "mdt wekelijks schrijven"],
                        "answer" => "Registreer wekelijks je gewerkte uren. Uren ophopen of compenseren is niet de bedoeling! ⏱️"
                    ],
                ]
            ],

            // ── LOONVERKLARING ────────────────────────────────────────────────
            "loon" => [
                "keywords" => ["loon", "loonverklaring", "salaris", "loonstrook", "uitbetaling"],
                "answer" => "Je loon wordt via een boekhoudingsbureau betaald. Je hebt een loonverklaring én ID kopie nodig. 💳",
                "suggestions" => [
                    "Loon betaling?",
                    "Wat heb ik nodig voor mijn loon?",
                    "Naar wie stuur ik mijn loonverklaring?"
                ],
                "sub_topics" => [
                    "hoe" => [
                        "keywords" => ["loon hoe wordt betaald", "loon betaling hoe werkt", "hoe wordt mijn loon betaald"],
                        "answer" => "De betaling van je loon gaat via een boekhoudingsbureau."
                    ],
                    "nodig" => [
                        "keywords" => ["loon wat heb ik nodig", "loonverklaring nodig id", "wat heb ik nodig voor mijn loon"],
                        "answer" => "Voor je uitbetaling is een loonverklaring én een kopie van je ID nodig."
                    ],
                    "sturen" => [
                        "keywords" => ["loonverklaring sturen naar", "loon email sturen", "naar wie stuur ik mijn loonverklaring"],
                        "answer" => "Stuur je loonverklaring naar boekhouding@technolableiden.nl. Zorg dat het op tijd aankomt!"
                    ],
                ]
            ],

            // ── VOG ───────────────────────────────────────────────────────────
            "vog" => [
                "keywords" => ["vog", "verklaring omtrent gedrag"],
                "answer" => "Een VOG (Verklaring Omtrent Gedrag) is verplicht om in het onderwijs te werken. 🏫",
                "suggestions" => [
                    "Wie vraagt de VOG aan?",
                    "Wat doe ik als ik mijn VOG ontvang?",
                    "Naar wie stuur ik mijn VOG?"
                ],
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["vog wat is uitleg", "verklaring omtrent gedrag wat"],
                        "answer" => "Een VOG (Verklaring Omtrent Gedrag) is verplicht om in het onderwijs te werken 🏫"
                    ],
                    "aanvragen" => [
                        "keywords" => ["vog aanvragen wie", "wie vraagt de vog aan", "vog technolab aanvragen"],
                        "answer" => "De VOG wordt voor jou aangevraagd door Technolab 👍"
                    ],
                    "ontvangen" => [
                        "keywords" => ["vog ontvangen doorsturen", "wat doe ik als ik mijn vog ontvang", "naar wie stuur ik mijn vog"],
                        "answer" => "Na ontvangst stuur je de VOG door naar de coördinator medewerker 📬"
                    ],
                ]
            ],

            // ── HUISREGELS ────────────────────────────────────────────────────
            "huisregels" => [
                "keywords" => ["huisregels"],
                "answer" => "Zorg dat je rond 8:15 uur binnen bent, dan starten we samen om 8:30 uur. 🕗",
                "suggestions" => [
                    "Wat doe ik als ik ziek ben volgens de huisregels?",
                    "Wat mag niet volgens de huisregels?",
                    "Welke klusjes horen bij de huisregels?"
                ],
                "sub_topics" => [
                    "tijd" => [
                        "keywords" => ["huisregels beginnen tijden", "huisregels 8 uur"],
                        "answer" => "Zorg dat je rond 8:15 uur binnen bent, dan starten we samen om 8:30 uur 🕗"
                    ],
                    "ziek" => [
                        "keywords" => ["huisregels ziek melden", "wat doe ik als ik ziek ben volgens de huisregels"],
                        "answer" => "Bel tussen 8:10 en 8:25 uur naar de dagco: 071-5191324 en zeg het je stagebegeleider 📞"
                    ],
                    "gedrag" => [
                        "keywords" => ["huisregels gedrag verboden", "wat mag niet volgens de huisregels", "huisregels telefoon kauwgom"],
                        "answer" => "Geen kauwgom, telefoon in tas, geen pet in de les, privé blijft privé. 🙅"
                    ],
                    "verlaten" => [
                        "keywords" => ["huisregels pand verlaten", "huisregels weggaan dagco"],
                        "answer" => "Verlaat je het pand? Meld het altijd bij de dagco 🚪"
                    ],
                    "klusjes" => [
                        "keywords" => ["huisregels klusjes opruimen", "welke klusjes horen bij de huisregels"],
                        "answer" => "Klusjes zoals opruimen horen erbij — wij zijn 1 team, 1 taak 💪"
                    ],
                ]
            ],

            // ── URENREGISTRATIE ───────────────────────────────────────────────
            "urenregistratie" => [
                "keywords" => ["urenregistratie", "uren schrijven", "uren registreren"],
                "answer" => "Registreer wekelijks je gewerkte uren. Uren ophopen of compenseren is niet de bedoeling! ⏱️",
                "suggestions" => [
                    "Hoe werkt de urenregistratie?",
                    "Mag ik uren opbouwen via urenregistratie?",
                    "Hoe pas ik mijn werkschema aan via urenregistratie?"
                ],
                "sub_topics" => [
                    "hoe" => [
                        "keywords" => ["urenregistratie hoe werkt", "hoe werkt de urenregistratie"],
                        "answer" => "Registreer wekelijks je gewerkte uren. Uren ophopen of compenseren is niet de bedoeling!⏱️"
                    ],
                    "opbouwen" => [
                        "keywords" => ["urenregistratie opbouwen compenseren", "mag ik uren opbouwen via urenregistratie"],
                        "answer" => "Uren opbouwen of compenseren is niet de bedoeling ❌ Bespreek meer werken met je rolverdeler."
                    ],
                    "schema" => [
                        "keywords" => ["urenregistratie schema aanpassen", "werkschema aanpassen rolverdeler", "hoe pas ik mijn werkschema aan via urenregistratie"],
                        "answer" => "Wijzigingen in het werkschema worden van tevoren afgesproken met de rolverdeler 📋"
                    ],
                ]
            ],

            // ── TECHNOLABBER ──────────────────────────────────────────────────
            "technolabber" => [
                "keywords" => [
                    "technolabber",
                    "technolab cultuur",
                    "technolab waarden",
                    "technolab gedragscode",
                    "kernwaarden technolab"
                ],
                "answer" => "Technolab Leiden is een leerwerkbedrijf met passie voor onderwijs, techniek, wetenschap en talentontwikkeling. ✨ Als Technolabber draag je die missie actief uit!",
                "suggestions" => [
                    "Wat zijn de kernwaarden van Technolab?",
                    "Hoe werkt Technolab intern?",
                    "Wat doet Technolab?"
                ],
                "sub_topics" => [
                    "kernwaarden" => [
                        "keywords" => ["kernwaarden van technolab", "waarden technolab principes", "zijn de kernwaarden van"],
                        "answer" => "Technolab heeft 5 kernwaarden: Samenwerken (duurzame relaties met scholen, bedrijven en overheid), Groeien (continu verbeteren), Bijdragen (handelen met impact voor een duurzame wereld), Leren (talentontwikkeling centraal) en Spelen (samen plezier hebben in werken en leren). 🌟"
                    ],
                    "missie" => [
                        "keywords" => ["missie technolab doel", "wat doet technolab missie", "waarom technolab bestaat"],
                        "answer" => "Technolab verbindt onderwijs, techniek en talentontwikkeling. We helpen kinderen, jongeren én medewerkers ontdekken: wie ben ik, wat kan ik, wat wil ik? We enthousiasmeren ze voor natuur en techniek — de toekomst! 🚀"
                    ],
                    "werkwijze" => [
                        "keywords" => ["hoe werkt technolab intern zelfsturend", "holacratie technolab scrum teams", "technolab teams werkwijze"],
                        "answer" => "Technolab werkt in zelfsturende teams op basis van holacratie en scrum. We denken in mogelijkheden en vertalen ideeën snel naar concrete acties. Technolab bruist van energie! ⚡"
                    ],
                    "activiteiten" => [
                        "keywords" => ["activiteiten technolab organiseert", "technolab programma workshops"],
                        "answer" => "Technolab organiseert workshops, projecten, beroepsoriëntatieweken en leerwerktrajecten. Ook bieden we trainingsprogramma's aan voor medewerkers van scholen en bedrijven, en begeleiden we mbo-, hbo- en wo-studenten bij hun praktijkervaring. 🎓"
                    ],
                    "impact" => [
                        "keywords" => ["technolab impact bereik leerlingen scholen", "hoeveel leerlingen technolab heeft"],
                        "answer" => "Elk jaar doen ruim 36.000 leerlingen, meer dan 50 scholen en circa 100 bedrijven en organisaties mee aan de lessen en projecten van Technolab. Zo slaan we de brug tussen onderwijs en arbeidsmarkt! 🌍"
                    ],
                ]
            ],

            // ── APP GROEP ─────────────────────────────────────────────────────
            "appgroep" => [
                "keywords" => ["appgroep", "signal app", "app groep werk"],
                "answer" => "We gebruiken Signal voor werk gerelateerde dingen. 📱 Geen WhatsApp!",
                "suggestions" => [
                    "Hoe meld ik me ziek via Signal?",
                    "Hoe meld ik me aan voor de Signal appgroep?",
                    "Welke app gebruiken we voor werk?"
                ],
                "sub_topics" => [
                    "welke" => [
                        "keywords" => ["welke app gebruiken we voor werk", "waarom signal niet whatsapp"],
                        "answer" => "We gebruiken Signal voor werk gerelateerde dingen 📱 Geen WhatsApp voor werkzaken!"
                    ],
                    "ziek" => [
                        "keywords" => ["signal ziek melden", "hoe meld ik me ziek via signal", "appgroep ziek melden"],
                        "answer" => "Ziekmeldingen moeten ook telefonisch doorgegeven worden aan de dagco. App alleen is niet genoeg! 📞"
                    ],
                    "aanmelden" => [
                        "keywords" => ["aanmelden signal groep", "hoe meld ik me aan voor de signal appgroep", "signal link joinen"],
                        "answer" => "Meld je aan via de link die je van Technolab krijgt 🔗"
                    ],
                ]
            ],

            // ── E-MAILHANDTEKENING ────────────────────────────────────────────
            "emailhandtekening" => [
                "keywords" => ["emailhandtekening", "email handtekening", "handtekening outlook"],
                "answer" => "Je emailhandtekening kan je aanpassen via de Technolab Handtekening Editor. Kopieer je handtekening en plak die in Outlook. ✉️",
                "suggestions" => [
                    "Hoe maak ik een emailhandtekening aan?",
                    "Hoe open ik de handtekening editor?",
                    "Hoe plak ik mijn handtekening in Outlook?"
                ],
                "sub_topics" => [
                    "aanmaken" => [
                        "keywords" => ["emailhandtekening aanmaken hoe", "hoe maak ik een emailhandtekening aan", "eerste handtekening outlook maken"],
                        "answer" => "Ga naar de Handtekening Editor op https://technolab-intern.nl/Emailhandtekening/, vul je persoonsgegevens in, en klik op de paarse knop 'Kopieer voor Outlook'. Plak dit daarna in Outlook via Instellingen → Account → Handtekeningen → Handtekening toevoegen. 📖"
                    ],
                    "editor" => [
                        "keywords" => ["handtekening editor openen", "hoe open ik de handtekening editor", "handtekening tool website"],
                        "answer" => "De Handtekening Editor open je via: https://technolab-intern.nl/Emailhandtekening/. Vul je gegevens in, kies of je een banner wil, en kopieer je handtekening via de paarse knop. 🖥️"
                    ],
                    "banner" => [
                        "keywords" => ["banner handtekening toevoegen", "emailhandtekening banner aanzetten"],
                        "answer" => "In de Handtekening Editor kan je een banner aanzetten door het vakje bij 'banner' aan te vinken. Er komen later mogelijk meer banner-opties — op 1 juli 2026 volgt de 'Techniek & Toekomst' banner. 🖼️"
                    ],
                    "plakken" => [
                        "keywords" => ["handtekening plakken outlook", "hoe plak ik mijn handtekening in outlook", "handtekening toevoegen outlook ctrl v"],
                        "answer" => "Kopieer je handtekening via de paarse knop in de editor. Ga in Outlook naar ⚙️ Instellingen → Account → Handtekeningen → Handtekening toevoegen. Geef hem een naam en plak met CTRL+V. Stel hem in als standaard voor nieuwe én doorgestuurde berichten en sla op. ✅"
                    ],
                    "svg" => [
                        "keywords" => ["svg handtekening werkt niet outlook", "svg bestand handtekening probleem"],
                        "answer" => "SVG-bestanden worden niet meegenomen als je de handtekening via CTRL+V plakt, omdat Outlook Word als engine gebruikt. Wil je toch SVG gebruiken? Klik in het preview-vlak van de editor, selecteer alles met CTRL+A en sleep de handtekening via drag & drop naar het handtekening-veld in Outlook. 🔧"
                    ],
                    "opslaan" => [
                        "keywords" => ["handtekening opslaan html", "html downloaden handtekening bewaren"],
                        "answer" => "Je kan je handtekening opslaan door de HTML-code te downloaden vanuit de editor. Zo kan je hem later opnieuw gebruiken zonder alles opnieuw in te vullen. 💾"
                    ],
                    "problemen" => [
                        "keywords" => ["handtekening werkt niet fout", "handtekening probleem ziet er raar uit", "handtekening mobiel"],
                        "answer" => "Op mobiele apparaten kan de handtekening er iets anders uitzien dan op desktop — dat is normaal. Gaat er iets fout? Neem contact op met Pieter via pieter@technolableiden.nl. 📬"
                    ],
                ]
            ],

            // ── HOLACRATIE / WERKOVERLEG ──────────────────────────────────────
            "holacratie" => [
                "keywords" => ["holacratie", "holacratisch", "werkoverleg cirkel"],
                "answer" => "Holacratisch werkoverleg is strak vergaderen volgens vaste regels. Elke cirkel heeft wekelijks zo'n overleg. 🔄",
                "suggestions" => [
                    "Wat is holacratie?",
                    "Wie is de facilitator bij holacratie?",
                    "Wat is een holacratie cirkel?"
                ],
                "sub_topics" => [
                    "wat" => [
                        "keywords" => ["holacratie wat is uitleg", "wat is holacratie holacratisch"],
                        "answer" => "Holacratisch werkoverleg is strak vergaderen volgens vaste regels. Elke cirkel heeft wekelijks zo'n overleg 📅"
                    ],
                    "facilitator" => [
                        "keywords" => ["holacratie facilitator secretaris", "wie is de facilitator bij holacratie"],
                        "answer" => "De facilitator (gekozen per periode) leidt het overleg. De secretaris zorgt dat taken in de teamsplanner worden vastgelegd ✍️"
                    ],
                    "cirkel" => [
                        "keywords" => ["holacratie cirkel wat is", "wat is een holacratie cirkel"],
                        "answer" => "Een cirkel is een team binnen Technolab. Elke cirkel heeft een eigen werkoverleg en planner 🔵"
                    ],
                ]
            ],

            // ── PLANNER ───────────────────────────────────────────────────────
            "planner" => [
                "keywords" => ["planner", "team agenda", "teamplanner"],
                "answer" => "Elke cirkel heeft een planner die de agenda beheert en veranderingen afstemmt. 📅",
                "suggestions" => [
                    "Hoe werkt de planner?",
                    "Waar vind ik het planner wachtwoord?",
                    "Wie staan er in de planner als teamleden?"
                ],
                "sub_topics" => [
                    "hoe" => [
                        "keywords" => ["planner hoe werkt agenda", "hoe werkt de planner"],
                        "answer" => "Elke cirkel heeft een planner die de agenda beheert en veranderingen afstemmt 📋"
                    ],
                    "wachtwoord" => [
                        "keywords" => ["planner wachtwoord toegang", "waar vind ik het planner wachtwoord"],
                        "answer" => "De agenda is vergrendeld met wachtwoord. Vraag het bij de hoofdplanner in overleg met je rolverdeler 🔒"
                    ],
                    "teamleden" => [
                        "keywords" => ["planner teamleden overzicht werkdagen", "wie staan er in de planner als teamleden"],
                        "answer" => "In de agenda zit een tabblad met alle teamleden, stagiairs en hun werkdagen. Zorg dat jij er ook bij staat! 👥"
                    ],
                ]
            ],

            // ── TOEKOMSTKUNDE (standalone) ────────────────────────────────────
            "toekomstkunde" => [
                "keywords" => [
                    "toekomstkunde",
                    "natuur en techniek lessen",
                    "natuur techniek po vo"
                ],
                "answer" => "Toekomstkunde is ons lesaanbod natuur- & technieklessen voor PO en VO, gericht op drie thema's: Energie en milieu, Technologische innovatie, en Leven en omgeving 🌱⚙️🌍",
                "suggestions" => [
                    "Wat is Energie en milieu?",
                    "Wat is Technologische innovatie?",
                    "Wat is Leven en omgeving?"
                ],
                "sub_topics" => [
                    "energie_milieu" => [
                        "keywords" => ["toekomstkunde energie en milieu", "duurzaamheid lessen toekomstkunde", "klimaatactie les toekomstkunde"],
                        "answer" => "Leerlingen onderzoeken hernieuwbare energie, biodiversiteit, circulaire economie en klimaatactie, en bedenken zelf oplossingen voor duurzaamheidsvraagstukken 🌱"
                    ],
                    "technologische_innovatie" => [
                        "keywords" => ["toekomstkunde technologische innovatie", "stroomcircuits tandwielen toekomstkunde"],
                        "answer" => "Leerlingen verkennen technologie en wetenschap via workshops over stroomcircuits, tandwielen en katrollen, en ontwikkelen creativiteit en probleemoplossend vermogen ⚙️"
                    ],
                    "leven_omgeving" => [
                        "keywords" => ["toekomstkunde leven en omgeving", "micro bit programmeren toekomstkunde", "robots bouwen toekomstkunde"],
                        "answer" => "Leerlingen leren programmeren via o.a. de Micro:bit en bouwen eigen robots om problemen op te lossen, en leggen zo een basis voor computatief denken 🤖"
                    ],
                    "groepen" => [
                        "keywords" => ["toekomstkunde lessen per groep", "voor welke groepen toekomstkunde", "groep 1 tot 8 vo mbo toekomstkunde"],
                        "answer" => "Er is lesaanbod voor groep 1/2 t/m groep 8, VO en MBO – van Robot Ontdeklab en Kleine Muis tot Hackerspace, DNA, Marsbots en Techniek en Duurzaamheid 📚"
                    ],
                    "onderzoekend_ontwerpend" => [
                        "keywords" => ["toekomstkunde onderzoekend leren ontwerpend", "wat is onderzoekend ontwerpend leren"],
                        "answer" => "Bij onderzoekend leren staat vragen stellen en experimenteren centraal, bij ontwerpend leren gaat het om bedenken, bouwen en testen van oplossingen in interactieve stappen 🔍"
                    ],
                    "locatie_lessen" => [
                        "keywords" => ["toekomstkunde lessen locatie", "kluslokaal technolab lessen"],
                        "answer" => "Lessen sluiten zoveel mogelijk aan op het curriculum en worden deels op school gegeven. Materiaalintensieve workshops worden bij Technolab gegeven, in een volledig uitgerust kluslokaal 🏫"
                    ],
                ]
            ],

            "zijinstromers" => [
                "keywords" => ["zijinstromers", "zij instromers", "carriere switch onderwijs", "overstap naar onderwijs"],
                "answer" => "Voor mensen die een carrièreswitch naar het onderwijs of de techniek overwegen, biedt Technolab drie programma's: Expeditie Leerkracht, Meesterchallenge en Techniek en Toekomst 🚀",
                "suggestions" => [
                    "Wat is Expeditie Leerkracht?",
                    "Wat is de Meesterchallenge?",
                    "Wat is Techniek en Toekomst?"
                ],
                "sub_topics" => [
                    "overzicht" => [
                        "keywords" => ["welke programmas voor zijinstromers", "opties zijinstromers overzicht"],
                        "answer" => "Expeditie Leerkracht is een tweedaagse kennismaking met het onderwijsvak, de Meesterchallenge is een 10 weken durend leer-werktraject in het onderwijs, en Techniek en Toekomst helpt je de stap te zetten naar de techniek 🎯"
                    ],
                ]
            ],

            "expeditie_leerkracht" => [
                "keywords" => ["expeditie leerkracht", "tweedaagse onderwijs", "kennismaken met onderwijs"],
                "answer" => "Expeditie Leerkracht is een tweedaagse waarin je op een actieve, speelse en persoonlijke manier je eerste stappen zet in het onderwijsvak. Een samenwerking tussen Hogeschool Leiden en Technolab 👩‍🏫",
                "suggestions" => [
                    "Wat kost Expeditie Leerkracht?",
                    "Wanneer is de volgende Expeditie Leerkracht?",
                    "Hoe ziet dag 1 en dag 2 eruit?"
                ],
                "sub_topics" => [
                    "kosten" => [
                        "keywords" => ["expeditie leerkracht kosten prijs", "wat kost expeditie leerkracht"],
                        "answer" => "De kosten zijn voor schooljaar 2025-2026 verlaagd van €500,- naar €250,-, dankzij bijdrage van de onderwijsregio's Leiden, Duin- en Bollenstreek en Haaglanden 💶"
                    ],
                    "data" => [
                        "keywords" => ["expeditie leerkracht data wanneer", "wanneer is de volgende expeditie leerkracht", "expeditie leerkracht aanmelden datum"],
                        "answer" => "Komende edities: 15-16 juni (VOL), 5-6 oktober 2026 (Den Haag, Inholland), 8-9 maart 2027 (Den Haag, HHS), 7-8 juni 2027 (Den Haag, Inholland) 📅"
                    ],
                    "programma" => [
                        "keywords" => ["expeditie leerkracht dag 1 dag 2", "hoe ziet dag 1 en dag 2 eruit", "expeditie leerkracht programma"],
                        "answer" => "Dag 1 (8:30-17:00): 'De drempel over' - kennismaken, klas op Technolab bekijken en zelf een mini-les ontwerpen en testen. Dag 2 (8:00-16:00): 'De beproeving' - zelf een les geven op een school 📖"
                    ],
                    "locatie" => [
                        "keywords" => ["expeditie leerkracht locatie waar", "expeditie leerkracht den haag"],
                        "answer" => "De edities vinden plaats in Den Haag, bij Inholland of HHS 📍"
                    ],
                ]
            ],

            "meesterchallenge" => [
                "keywords" => ["meesterchallenge", "10 weken challenge onderwijs", "leer werktraject onderwijs"],
                "answer" => "De Meesterchallenge is een 10 weken durende challenge waarbij je 3 dagen per week, samen met je team, workshops ontwikkelt en geeft binnen natuur, techniek en technologie. Ideaal als tussenjaar of carrièreswitch! 🎓",
                "suggestions" => [
                    "Hoeveel vergoeding krijg ik bij de Meesterchallenge?",
                    "Wanneer kan ik starten met de Meesterchallenge?",
                    "Voor wie is de Meesterchallenge?"
                ],
                "sub_topics" => [
                    "vergoeding" => [
                        "keywords" => ["meesterchallenge vergoeding geld", "hoeveel verdien je meesterchallenge"],
                        "answer" => "Voor de Meesterchallenge ontvang je een vergoeding van €700 💶"
                    ],
                    "data" => [
                        "keywords" => ["meesterchallenge data wanneer startdata", "wanneer kan ik starten met de meesterchallenge"],
                        "answer" => "Komende periodes: 14 sept t/m 27 nov 2026, 26 okt t/m 15 jan 2027, 4 jan t/m 19 mrt 2027, 15 mrt t/m 4 juni 2027, 10 mei t/m 16 juli 2027 (Technolab is dicht in schoolvakanties) 📅"
                    ],
                    "doelgroep" => [
                        "keywords" => ["voor wie meesterchallenge doelgroep", "meesterchallenge eisen diploma"],
                        "answer" => "Voor iedereen die zijn/haar talenten wil ontdekken: een tussenjaar, carrièreswitch of zij-instroomtraject. Geen diploma of bèta-achtergrond nodig, wel een aanpakker die houdt van doen 🙌"
                    ],
                    "inhoud" => [
                        "keywords" => ["meesterchallenge programma trainingen", "wat leer je bij meesterchallenge"],
                        "answer" => "Je ontwikkelt en geeft workshops in teamverband en krijgt trainingen 'pedagogiek en didactiek' en 'persoonlijke ontwikkeling', waarbij je leert wat actief leren is en hoe je orde houdt 📚"
                    ],
                    "sollicitatie" => [
                        "keywords" => ["meesterchallenge solliciteren aanmelden", "meesterchallenge procedure meeloopdag"],
                        "answer" => "De procedure bestaat uit een kennismakingscall met een coach en een meeloopdag, waarna jullie samen ontdekken of er een match is ✅"
                    ],
                ]
            ],

            "techniek_en_toekomst" => [
                "keywords" => ["techniek en toekomst", "techniek toekomst leerwerktraject"],
                "answer" => "Techniek & Toekomst verbindt bedrijven met technisch talent. Het bestaat uit drie stappen: een Expeditie (2 dagen), een Challenge (2 weken) en een Stage (10 weken) 🔧",
                "suggestions" => [
                    "Wat is de Expeditie Techniek en Toekomst?",
                    "Wat is de Challenge?",
                    "Wat is de Stage bij Techniek en Toekomst?"
                ],
                "sub_topics" => [
                    "expeditie" => [
                        "keywords" => ["expeditie techniek en toekomst 2 dagen", "wanneer expeditie techniek toekomst"],
                        "answer" => "De Expeditie duurt 2 dagen: je zet op een actieve manier je eerste stappen in de wereld van techniek en ontdekt welke sector bij je past. Komende editie: 29-30 juni 2026 📅"
                    ],
                    "challenge" => [
                        "keywords" => ["techniek en toekomst challenge 2 weken opdracht"],
                        "answer" => "De Challenge duurt 2 weken: je werkt aan een echte techniekopdracht bij een bedrijf en verdiept je in een specifieke sector 🔨"
                    ],
                    "stage" => [
                        "keywords" => ["techniek en toekomst stage 10 weken bedrijf"],
                        "answer" => "De Stage duurt 10 weken: je loopt drie dagen per week mee bij een bedrijf in jouw gekozen sector, gericht op een opleiding of baan in de techniek 💼"
                    ],
                    "aanmelden" => [
                        "keywords" => ["aanmelden techniek en toekomst formulier"],
                        "answer" => "Je kunt je aanmelden voor de Expeditie Techniek en Toekomst via het aanmeldformulier op de website, of contact opnemen voor meer informatie 📝"
                    ],
                ]
            ],

            "bedrijven" => [
                "keywords" => ["bedrijven partner technolab", "samenwerking bedrijven technolab"],
                "answer" => "Technolab werkt samen met bedrijven via Techniek en Toekomst (technisch talent vinden), Talent & Toekomst (loopbaanoriëntatie voor scholieren) en workshops voor volwassen teams 🤝",
                "suggestions" => [
                    "Wat is Techniek en Toekomst voor bedrijven?",
                    "Wat is Talent & Toekomst?",
                    "Bieden jullie ook workshops voor teams?"
                ],
                "sub_topics" => [
                    "techniek_toekomst_bedrijven" => [
                        "keywords" => ["bedrijven techniek en toekomst talent", "technisch talent vinden bedrijven"],
                        "answer" => "Via Techniek en Toekomst vinden technisch talent en bedrijven elkaar: van oriënteren tot opleiden, wij maken de stap van dromen naar doen 🔧"
                    ],
                    "talent_toekomst_bedrijven" => [
                        "keywords" => ["talent en toekomst voor bedrijven stage mavo"],
                        "answer" => "Bij Talent & Toekomst lopen mavoleerlingen van het Bonaventura College stage bij bedrijven zoals PLNT, Kleine Planeet en Easyfiets, in sectoren als ICT, Ondernemen, Onderwijs en Zorg 🏢"
                    ],
                    "workshops_volwassenen" => [
                        "keywords" => ["workshops teams volwassenen technolab", "teamuitje technolab duurzaamheid robotica"],
                        "answer" => "Ben je op zoek naar een unieke manier om je team aan te zetten voor duurzaamheid, robotica of de digitale wereld? Meld je team aan voor een workshopdag op Technolab. Binnenkort meer informatie 🛠️"
                    ],
                    "partners" => [
                        "keywords" => ["partners technolab wie zijn de partners"],
                        "answer" => "Onder andere Ondernemersfonds Leiden, Plus, DZB, MBO Rijnland, UWV, Hortus Botanicus, Leiden Bio Science Park, CHDR, Gemeente Leiden, Zooma, Holland Rijnland en meer 🤝"
                    ],
                ]
            ],

            "talent_en_toekomst" => [
                "keywords" => ["talent en toekomst", "vijfdaagse stage leerlingen", "stage vo leerlingen"],
                "answer" => "Talent & Toekomst is een vijfdaagse activerende stage voor VO-leerlingen om alle ins en outs van vier vakgebieden te ontdekken: Ondernemen, Onderwijs, Techniek en Zorg 🧭",
                "suggestions" => [
                    "Voor welke leerlingen is Talent & Toekomst?",
                    "Bij welke bedrijven loop je stage?",
                    "Hoe werkt de stageweek?"
                ],
                "sub_topics" => [
                    "doelgroep" => [
                        "keywords" => ["talent en toekomst doelgroep voor welke school"],
                        "answer" => "Talent & Toekomst is bedoeld voor middelbare scholieren, momenteel mavoleerlingen van het Bonaventura College 🎓"
                    ],
                    "sectoren" => [
                        "keywords" => ["talent en toekomst sectoren vakgebieden"],
                        "answer" => "Leerlingen verkennen vier sectoren: ICT, Ondernemen, Onderwijs en Zorg 🔍"
                    ],
                    "bedrijven_stage" => [
                        "keywords" => ["talent en toekomst stagebedrijven", "bij welke bedrijven loop je stage talent toekomst"],
                        "answer" => "Leerlingen lopen stage bij bedrijven zoals PLNT, Kleine Planeet en Easyfiets 🏢"
                    ],
                    "doel" => [
                        "keywords" => ["doel talent en toekomst waarom", "hoe werkt de stageweek talent toekomst"],
                        "answer" => "Het programma helpt leerlingen groeien in beroepsbeelden, beroepsgerichte kennis en vaardigheden, zodat ze een betere studiekeuze kunnen maken 🎯"
                    ],
                ]
            ],

            // ── BUDDY / COACHING ──────────────────────────────────────────────
            "coaching" => [
                "keywords" => ["buddy coaching", "coach technolab"],
                "answer" => "Elke medewerker zoekt een buddy om leerdoelen te bespreken. Coaches helpen met persoonlijke uitdagingen. 🤝",
                "suggestions" => [
                    "Wat is het buddy systeem?",
                    "Hoe werkt het coaching traject?",
                    "Wie is mijn coach?"
                ],
                "sub_topics" => [
                    "buddy" => [
                        "keywords" => ["buddy systeem leerdoel technolab", "wat is het buddy systeem"],
                        "answer" => "Elke medewerker zoekt een buddy binnen Technolab om eigen leerdoelen te bespreken en te evalueren 🎯"
                    ],
                    "coach" => [
                        "keywords" => ["coaching traject afspraken", "hoe werkt het coaching traject"],
                        "answer" => "Coaches helpen in een traject van 3-4 afspraken met persoonlijke uitdagingen 💬"
                    ],
                    "wie" => [
                        "keywords" => ["coach wie is mijn coach", "coaching organigram talentontwikkeling"],
                        "answer" => "Cirkel Talentontwikkeling verzorgt Coaching en trainingen. Zie het organigram voor wie op dit moment coach is 🗂️"
                    ],
                ]
            ],

            // ── VERTROUWENSPERSOON ────────────────────────────────────────────
            "vertrouwenspersoon" => [
                "keywords" => ["vertrouwenspersoon", "vertrouwelijk bespreken"],
                "answer" => "Heb je iets vertrouwelijks te bespreken? Ga naar onze vertrouwenspersoon! 🔒",
                "suggestions" => [
                    "Wie is de vertrouwenspersoon?",
                ],
                "sub_topics" => [
                    "wie" => [
                        "keywords" => ["wie is de vertrouwenspersoon", "vertrouwenspersoon naam"],
                        "answer" => "Maartje Kapteijn is onze vertrouwenspersoon."
                    ]
                ]
            ],

            // ── BUS RIJDEN ────────────────────────────────────────────────────
            "bus" => [
                "keywords" => ["bus rijden", "bus reserveren technolab"],
                "answer" => "Heb je een rijbewijs? Dan moet je eerst een proefrit doen. Daarna mag je ermee rijden! 🚐",
                "suggestions" => [
                    "Hoe reserveer ik de bus?",
                    "Wat zijn de regels voor het rijden met de bus?"
                ],
                "sub_topics" => [
                    "rijden" => [
                        "keywords" => ["bus rijden proefrit rijbewijs", "wat zijn de regels voor het rijden met de bus"],
                        "answer" => "Heb je een rijbewijs? Dan moet je eerst een proefrit met de Technolab bus doen. Pas daarna mag je ermee rijden 🚗"
                    ],
                    "reserveren" => [
                        "keywords" => ["bus reserveren dagco wiki", "hoe reserveer ik de bus", "fiets reserveren technolab"],
                        "answer" => "Reserveer via de Dagco Wiki! Dit geldt ook voor fietsen! 📅"
                    ],
                ]
            ],

            // ── BOEKHOUDING / INKOPEN ─────────────────────────────────────────
            "boekhouding" => [
                "keywords" => ["boekhouding", "inkopen technolab", "bonnetje declareren", "pinpas technolab"],
                "answer" => "Bij Gamma of Plus koop je met je Technolab pasje. Bonnetje inleveren in de kast in de Groei! 🧾",
                "suggestions" => [
                    "Hoe betaal ik zelf iets voor boekhouding?",
                    "Hoe gebruik ik de pinpas voor boekhouding?",
                    "Hoe bestel ik iets online via boekhouding?"
                ],
                "sub_topics" => [
                    "gamma_plus" => [
                        "keywords" => ["boekhouding gamma plus pasje", "inkopen gamma plus technolab"],
                        "answer" => "Bij de Gamma of Plus koop je met je Technolab pasje 🪪 Neem bij de Gamma ook de Gamma-pas mee! Bonnetje in kast in de Groei!"
                    ],
                    "voorschieten" => [
                        "keywords" => ["boekhouding zelf betalen voorschieten", "hoe betaal ik zelf iets voor boekhouding", "declareren terugkrijgen"],
                        "answer" => "Stuur foto van bonnetje + rekeninggegevens naar boekhouding@technolableiden.nl ✉️ Vraag altijd akkoord van producteigenaar!"
                    ],
                    "pinpas" => [
                        "keywords" => ["boekhouding pinpas gebruiken code", "hoe gebruik ik de pinpas voor boekhouding"],
                        "answer" => "Foto van bonnetje naar boekhouding en origineel in kast in Groei 🧾 Vraag waar pinpas en code zijn!"
                    ],
                    "online" => [
                        "keywords" => ["boekhouding online bestellen", "hoe bestel ik iets online via boekhouding"],
                        "answer" => "Stuur op tijd een link naar boekhouding — liefst met akkoord van producteigenaar 🛒"
                    ],
                    "overig" => [
                        "keywords" => ["boekhouding overige kosten geen bonnetje", "parkeren bus wassen boekhouding"],
                        "answer" => "Andere uitgaven zonder bonnetje? Bespreek met boekhouding, we vinden samen een oplossing 🤝"
                    ],
                    "voorraad" => [
                        "keywords" => ["boekhouding voorraad op toiletpapier koffie", "voorraad technolab op raakt"],
                        "answer" => "Voorraad op raakt? Laat het boekhouding/inkoop weten! 📦"
                    ],
                ]
            ],

            // ── PAPIER HERGEBRUIKEN ───────────────────────────────────────────
            "papier" => [
                "keywords" => ["papier hergebruiken", "papier recyclen", "papier brein", "papier dubbelzijdig"],
                "answer" => "Op Technolab hergebruiken we papier! ♻️ Sorteer papier in het brein: dubbelzijdig of engelszijdig bedrukt.",
            ],
        ];
        $this->buildSuggestionMap();
    }

    private function initializeSynonyms(): void
    {
        $this->synonyms = [
            "loon" => ["salaris", "betaling", "uitbetaling", "verdienste", "inkomsten"],
            "betaling" => ["uitbetaling", "loon", "salaris", "geld", "giro"],
            "lessen" => ["les", "workshop", "lesaanbod", "lesprogramma", "les geven", "training"],
            "techniekwijs" => ["techniek", "wetenschap", "electronics", "maker"],
            "programmeren" => ["coderen", "coding", "digitaal", "toekomsttaal"],
            "duurzaamheid" => ["groen", "klimaat", "toekomstkunde", "milieu"],
            "dagco" => ["dagcoordinator", "dagcoördinator", "coordinator"],
            "stage" => ["stagiair", "stageplaats", "stagelopen", "praktijk"],
            "holacratie" => ["werkoverleg", "cirkel", "team overleg", "cirkeloverleg"],
            "wie" => ["welke persoon", "naam", "contact"],
            "wat" => ["welke", "soort", "type"],
            "hoe" => ["op welke manier", "werkwijze", "proces"],
            "waar" => ["locatie", "plek", "plaats", "adres"],
            "wanneer" => ["tijdstip", "moment", "dag", "uur"],
            "projectdag" => ["project dag", "project voor leerlingen", "basisschool project"],
        ];
    }

    private function initializeSemanticGroups(): void
    {
        $this->semanticGroups = [
            "financial" => ["loon", "salaris", "betaling", "geld", "verdienen", "uitbetaling", "pensioen"],
            "education" => ["lessen", "les", "workshop", "techniekwijs", "toekomsttaal", "toekomstkunde", "programmeren"],
            "organization" => ["dagco", "holacratie", "cirkel", "team", "planner", "werkoverleg"],
            "work" => ["stage", "werk", "job", "stagiair", "medewerker", "contract"],
            "rules" => ["huisregels", "gedrag", "regels", "protocol", "richtlijn"],
            "projects" => ["projectdag", "botsende bots", "groene daken", "mens en robot", "duurzaam huis"],
        ];
    }

    private function buildSuggestionMap(): void
    {
        foreach ($this->intents as $intentName => $intent) {
            if (!isset($intent["suggestions"]) || !isset($intent["sub_topics"])) {
                continue;
            }

            foreach ($intent["suggestions"] as $suggestion) {
                $bestMatch = $this->findSubTopicForSuggestion($intentName, $suggestion);
                if ($bestMatch) {
                    $normalizedSuggestion = $this->normalizeMessage($suggestion);
                    $this->suggestionMap[$normalizedSuggestion] = [$intentName, $bestMatch];
                }
            }
        }
    }

    private function findSubTopicForSuggestion(string $intentName, string $suggestion): ?string
    {
        $intent = $this->intents[$intentName];
        $suggestionWords = $this->tokenizeMessage($this->normalizeMessage($suggestion));

        $bestMatch = null;
        $bestScore = 0;

        foreach ($intent["sub_topics"] as $subKey => $subTopic) {
            $keywords = $subTopic["keywords"] ?? [];
            $score = 0;

            foreach ($keywords as $keyword) {
                $keywordScore = $this->calculateWordToKeywordSemanticScore($this->normalizeMessage($suggestion), $suggestionWords, $keyword);
                $score = max($score, $keywordScore);
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $subKey;
            }
        }

        return $bestMatch;
    }

    private function findSuggestionMatch(string $normalizedMessage): ?array
    {
        if (isset($this->suggestionMap[$normalizedMessage])) {
            return $this->suggestionMap[$normalizedMessage];
        }

        foreach ($this->suggestionMap as $suggestion => $data) {
            $similarity = $this->stringSimilarity($normalizedMessage, $suggestion);
            if ($similarity > 0.85) {
                return $data;
            }
        }

        return null;
    }

    private function stringSimilarity(string $a, string $b): float
    {
        $distance = levenshtein($a, $b);
        $maxLength = max(strlen($a), strlen($b));
        if ($maxLength === 0) {
            return 1.0;
        }
        return 1 - ($distance / $maxLength);
    }

    private function normalizeMessage(string $message): string
    {
        $message = mb_strtolower(trim($message));
        $message = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $message);
        $message = preg_replace('/\s+/', ' ', $message);
        return trim($message);
    }

    private function tokenizeMessage(string $message): array
    {
        return array_filter(explode(' ', $message), fn($w) => strlen($w) >= 2);
    }

    public function respond(string $message): array
    {
        $normalizedMessage = $this->normalizeMessage($message);
        $messageWords = $this->tokenizeMessage($normalizedMessage);

        $this->conversationHistory[] = [
            "message" => $message,
            "normalized" => $normalizedMessage,
            "timestamp" => time()
        ];
        $_SESSION["conversation_history"] = $this->conversationHistory;

        $suggestionMatch = $this->findSuggestionMatch($normalizedMessage);
        if ($suggestionMatch) {
            [$intentName, $subTopicKey] = $suggestionMatch;
            return $this->getResponseForSuggestion($intentName, $subTopicKey, $normalizedMessage);
        }

        $allResults = $this->scoreAllIntents($normalizedMessage, $messageWords);

        if (empty($allResults) || $allResults[0]["score"] < 0.4) {
            return [
                "reply" => $this->defaultResponse(),
                "buttons" => []
            ];
        }

        $topScore = $allResults[0]["score"];
        $tied = array_filter($allResults, fn($r) => ($topScore - $r["score"]) <= self::TIE_THRESHOLD);
        $tied = array_values($tied);

        $tiedTopLevelIntents = [];
        foreach ($tied as $result) {
            $intentName = $result["intent"];
            if (!isset($tiedTopLevelIntents[$intentName])) {
                $tiedTopLevelIntents[$intentName] = $result;
            }
        }

        if (count($tiedTopLevelIntents) >= 2) {
            return $this->buildTieResponse($tiedTopLevelIntents);
        }

        $winner = $allResults[0];
        $winnerIntent = $winner["intent"];

        $bestSubTopic = null;
        foreach ($allResults as $result) {
            if ($result["intent"] === $winnerIntent && $result["subTopic"] !== null) {
                $bestSubTopic = $result;
                break;
            }
        }

        $subTopicToUse = null;
        if ($bestSubTopic !== null && $bestSubTopic["score"] >= 0.85) {
            $subTopicToUse = $bestSubTopic["subTopic"];
        }

        return $this->getResponse(
            $winnerIntent,
            $this->intents[$winnerIntent],
            $normalizedMessage,
            $subTopicToUse
        );
    }

    private function scoreAllIntents(string $normalizedMessage, array $messageWords): array
    {
        $results = [];

        foreach ($this->intents as $intentName => $intent) {
            $intentScore = $this->calculateIntentSemanticScore($normalizedMessage, $messageWords, $intent);
            $results[] = [
                "intent"   => $intentName,
                "subTopic" => null,
                "score"    => $intentScore,
            ];

            if (isset($intent["sub_topics"])) {
                foreach ($intent["sub_topics"] as $subKey => $subTopic) {
                    $subScore = $this->calculateIntentSemanticScore($normalizedMessage, $messageWords, $subTopic);
                    $results[] = [
                        "intent"   => $intentName,
                        "subTopic" => $subKey,
                        "score"    => $subScore,
                    ];
                }
            }
        }

        usort($results, fn($a, $b) => $b["score"] <=> $a["score"]);

        return $results;
    }

    private function calculateIntentSemanticScore(string $normalizedMessage, array $messageWords, array $intentData): float
    {
        $keywords = $intentData["keywords"] ?? [];
        if (empty($keywords)) {
            return 0.0;
        }

        $maxScore = 0.0;
        $matchCount = 0;

        foreach ($keywords as $keyword) {
            $keywordScore = $this->calculateWordToKeywordSemanticScore($normalizedMessage, $messageWords, $keyword);

            if ($keywordScore > $maxScore) {
                $maxScore = $keywordScore;
            }

            if ($keywordScore > 0.3) {
                $matchCount++;
            }
        }

        $multiMatchBonus = min(($matchCount - 1) * 0.05, 0.15);

        return min($maxScore + $multiMatchBonus, 1.0);
    }

    private function calculateWordToKeywordSemanticScore(string $normalizedMessage, array $messageWords, string $keyword): float
    {
        if (str_contains($normalizedMessage, $keyword)) {
            return 1.0;
        }

        $bestScore = 0.0;

        foreach ($messageWords as $word) {
            if ($word === $keyword) {
                return 1.0;
            }

            $bestScore = max($bestScore, $this->fuzzyWordScore($word, $keyword));
            $bestScore = max($bestScore, $this->synonymWordScore($word, $keyword));
            $bestScore = max($bestScore, $this->semanticGroupScore($word, $keyword));
        }

        return $bestScore;
    }

    private function fuzzyWordScore(string $word, string $keyword): float
    {
        if (strlen($word) < 2 || strlen($keyword) < 2) {
            return 0;
        }

        if (abs(strlen($word) - strlen($keyword)) > 3) {
            return 0;
        }

        $distance = levenshtein($word, $keyword);
        $maxLength = max(strlen($word), strlen($keyword));

        if ($maxLength === 0) {
            return 0;
        }

        $similarity = 1 - ($distance / $maxLength);
        return ($similarity >= 0.75) ? $similarity * 0.85 : 0;
    }

    private function synonymWordScore(string $word, string $keyword): float
    {
        if (isset($this->synonyms[$keyword]) && in_array($word, $this->synonyms[$keyword])) {
            return 0.8;
        }
        if (isset($this->synonyms[$word]) && in_array($keyword, $this->synonyms[$word])) {
            return 0.8;
        }
        return 0;
    }

    private function semanticGroupScore(string $word, string $keyword): float
    {
        foreach ($this->semanticGroups as $groupWords) {
            if (in_array($word, $groupWords) && in_array($keyword, $groupWords)) {
                return 0.65;
            }
        }
        return 0;
    }

    private function buildTieResponse(array $tiedIntents): array
    {
        $buttons = [];

        foreach ($tiedIntents as $intentName => $result) {
            $intent = $this->intents[$intentName];
            $label = $intent["suggestions"][0] ?? ucfirst($intentName);
            $buttons[] = [
                "label" => $label,
                "value" => $label,
            ];
        }

        return [
            "reply"   => "Ik weet het niet zeker 🤔 Bedoel je één van deze onderwerpen?",
            "buttons" => array_slice($buttons, 0, 4),
        ];
    }

    private function getResponseForSuggestion(string $intentName, string $subTopicKey, string $normalizedClickedSuggestion): array
    {
        $intent = $this->intents[$intentName];
        $subTopic = $intent["sub_topics"][$subTopicKey] ?? null;

        if (!$subTopic) {
            return ["reply" => "Could not find that answer", "buttons" => []];
        }

        $response = [
            "reply" => $subTopic["answer"],
            "buttons" => []
        ];

        if (isset($subTopic["image"])) {
            $response["image"] = $subTopic["image"];
        }

        if (isset($intent["suggestions"])) {
            $suggestionCount = 0;
            foreach ($intent["suggestions"] as $suggestion) {
                $normalizedSuggestion = $this->normalizeMessage($suggestion);
                if ($normalizedSuggestion === $normalizedClickedSuggestion) {
                    continue;
                }
                if ($suggestionCount < 3) {
                    $response["buttons"][] = [
                        "label" => $suggestion,
                        "value" => $suggestion
                    ];
                    $suggestionCount++;
                }
            }
        }

        return $response;
    }

    private function getResponse(
        string $intentName,
        array $intent,
        string $message,
        ?string $subTopic = null
    ): array {

        if ($intentName === "hallo") {
            $greeting = $this->extractGreeting($message, $intent["keywords"]);
            $answer = ucfirst($greeting) . "! Fijn je te ontmoeten! Waar kan ik je mee helpen?";

            $buttons = [];
            foreach (array_slice($intent["suggestions"] ?? [], 0, 3) as $suggestion) {
                $buttons[] = ["label" => $suggestion, "value" => $suggestion];
            }
            return ["reply" => $answer, "buttons" => $buttons];
        }

        if ($subTopic && isset($intent["sub_topics"][$subTopic])) {
            $subTopicData = $intent["sub_topics"][$subTopic];
            $response = ["reply" => $subTopicData["answer"], "buttons" => []];
            if (isset($subTopicData["image"])) {
                $response["image"] = $subTopicData["image"];
            }
            return $response;
        }

        $buttons = [];
        foreach (array_slice($intent["suggestions"] ?? [], 0, 3) as $suggestion) {
            $buttons[] = ["label" => $suggestion, "value" => $suggestion];
        }

        $response = ["reply" => $intent["answer"], "buttons" => $buttons];

        if (isset($intent["image"])) {
            $response["image"] = $intent["image"];
        }

        return $response;
    }

    private function extractGreeting(string $message, array $keywords): string
    {
        $normalizedMessage = $this->normalizeMessage($message);
        $messageWords = $this->tokenizeMessage($normalizedMessage);

        foreach ($keywords as $keyword) {
            if (str_contains($normalizedMessage, $keyword)) {
                return $keyword;
            }

            if (!str_contains($keyword, ' ')) {
                foreach ($messageWords as $word) {
                    if ($this->fuzzyWordScore($word, $keyword) > 0.8 || $word === $keyword) {
                        return $keyword;
                    }
                }
            }
        }

        return "hallo";
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
$message = strip_tags(trim($message));
$message = mb_substr($message, 0, 500);
if ($message === "") {
    echo json_encode(["reply" => "", "buttons" => []]);
    exit;
}

$bot = new TechnoBot();
$response = $bot->respond($message);
echo json_encode($response);
