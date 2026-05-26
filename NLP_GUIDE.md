# TechnoBot - Natural Language Processing (NLP) Features

## 🧠 What's New: Smart Language Understanding

TechnoBot now uses **Compromise.js**, a powerful NLP library, to understand user messages in context and extract meaningful information automatically.

---

## ✨ NLP Features Implemented

### 1. **Semantic Understanding** 🎯
The bot now understands questions even when exact keywords aren't used.

**Examples:**
- "What's my salary here?" → Understands you're asking about "loon" (salary)
- "When do we eat together?" → Understands this is about "fika" (cooking together)
- "How to request a card?" → Maps to "pasje" topic

**Semantic mappings include:**
```
salaris, betaling, inkomen → loon (salary)
eten, koken, maaltijd → fika (cooking)
nood, emergency, ongeluk → bhv (emergency help)
deur, toegang → pasje (access card)
... and many more
```

---

### 2. **Entity Extraction** 📊

The bot automatically extracts important information from your messages:

#### A. **Amount/Money Extraction**
Recognizes financial values in messages:
```
"Hoeveel mag ik uitgeven?" 
  → Extracts: €50, 50 euro → Adds context for salary questions
  
"Rond de €50 voor Fika"
  → Extracted amount helps answer budget questions
```

#### B. **Date/Time Extraction**
Identifies temporal references:
```
"Wanneer is het?"
  → Extracts: woensdag, week, maand
  → Adds context: this is a time question
  
"Tussen 23:00 en 06:00"
  → Extracts: 23:00, 06:00
  → Understands time constraints
```

#### C. **Question Type Detection**
Recognizes what kind of question is being asked:
```
"Hoeveel?" → amount_question
"Wanneer?" → time_question  
"Waar?" → location_question
"Wie?" → person_question
"Hoe?" → method_question
"Wat?" → definition_question
```

---

### 3. **Linguistic Analysis** 🗣️

Compromise.js provides deep language analysis:

#### Nouns (Key Concepts)
```javascript
doc.nouns().out("array")
// Extracts key subjects from your message
```

#### Verbs (Actions)
```javascript
doc.verbs().out("array")
// Understands what action you're asking about
```

#### Numbers & Values
```javascript
doc.numbers().out("value")
// Extracts all numeric values (salaries, times, budgets)
```

#### People & Organizations
```javascript
doc.people().out("array")
doc.organizations().out("array")
// Identifies names and companies mentioned
```

---

### 4. **Context-Aware Matching** 🔗

The bot remembers your conversation context and uses it to improve responses:

```
User: "Tell me about salary"
  → Bot sets context: "loon" (salary topic)
  
User: "How much can I get?"
  → Bot understands this relates to salary, not generic "how much"
  → Provides salary-specific answer
```

---

## 📋 How NLP Works (Behind the Scenes)

### Frontend (JavaScript - Script.js):
1. User types a message
2. **Compromise.js analyzes** the text
3. Extracts: nouns, verbs, numbers, dates, entities
4. Finds semantic mappings (salary question → "loon" topic)
5. Sends enriched data to backend:
   ```javascript
   {
     message: "What about salary?",
     semantic_match: { intent: "loon", confidence: 0.8 },
     extracted_nouns: "salary, money",
     extracted_numbers: "50, 100",
     extracted_dates: "wednesday"
   }
   ```

### Backend (PHP - Chat.php):
1. **Receives NLP data** from frontend
2. Tries **semantic matching first** (very accurate)
3. Falls back to context matching
4. Then tries fuzzy keyword matching
5. Returns appropriate response

---

## 🎯 Matching Hierarchy (Order of Accuracy)

1. **Semantic Matching** (Highest - from NLP) ⭐⭐⭐⭐⭐
   - Uses AI understanding of your question
   - Example: "salary" → knows you mean "loon"

2. **Context Matching** ⭐⭐⭐⭐
   - Uses conversation history
   - Example: Already discussing salary, follow-up understood

3. **Fuzzy Keyword Matching** ⭐⭐⭐
   - Uses Levenshtein distance similarity
   - Example: "saelary" matches "salary" with 75% similarity

4. **Exact Match** ⭐⭐
   - Direct substring match
   - Example: Message contains exact keyword

---

## 📊 Semantic Topic Mappings

### Salary & Payment (loon)
```
Mapped terms: salaris, betaling, uitbetaling, inkomen, geld
Entity type: amount_question (hoeveel?)
```

### Cooking Together (fika)
```
Mapped terms: eten, koken, lunch, maaltijd, boodschappen
Entity type: time_question (wanneer?)
```

### Emergency Help (bhv)
```
Mapped terms: nood, emergency, hulp, ongeluk
Entity type: definition_question (wat is?)
```

### Access Card (pasje)
```
Mapped terms: deur, toegang, pas, liftpas, sleutel, alarm
Entity type: method_question (hoe?)
```

### Working Hours (mdt)
```
Mapped terms: uren, werken, jonger, subsidie, registreren
Entity type: amount_question (hoeveel uren?)
```

### House Rules (huisregels)
```
Mapped terms: werk, werkuren, regels, ziek, verhinderd, telefoon
Entity type: definition_question (wat zijn de regels?)
```

---

## 💡 Example Conversations

### Example 1: Smart Salary Question
```
User: "How much do I get paid every month?"
  NLP Analysis:
    - Nouns: paid, month
    - Numbers: extracted amounts
    - Question type: amount_question
    - Semantic match: "paid" → "loon"
    - Confidence: 0.8
  
Bot: "De betaling van je loon gaat via een boekhoudingsbureau"
```

### Example 2: Date Understanding
```
User: "When do we cook together?"
  NLP Analysis:
    - Nouns: cook, together
    - Question type: time_question
    - Dates extracted: [woensdag, week]
    - Semantic match: "cook, together" → "fika"
    - Confidence: 0.9
  
Bot: "Fika is elke woensdag 📅"
```

### Example 3: Money Question in Context
```
User: "What's the budget?"
  NLP Analysis:
    - Nouns: budget
    - Question type: amount_question
    - Previous context: fika (cooking)
    - Combined understanding: Budget for fika
  
Bot: "Er mag rond de €50,- voor Fika worden uitgegeven 💶"
```

---

## 🔧 Customization Guide

### Add New Semantic Mappings

Edit the `SEMANTIC_MAPPINGS` object in **Script.js**:

```javascript
const SEMANTIC_MAPPINGS = {
    // Map user questions to actual intents
    "your_word": "intent_name",
    "another_word": "another_intent",
};
```

### Add Topic Keywords for Entity Extraction

In **Chat.php**, edit `extractEntitiesFromMessage()`:

```php
$topicKeywords = [
    "your_intent" => ["keyword1", "keyword2", "keyword3"],
];
```

### Add New Entity Types

Extend `extractEntitiesFromMessage()` to recognize more patterns:

```php
// Extract emails
if (preg_match_all('/[\w\.-]+@[\w\.-]+/', $message, $matches)) {
    $entities["emails"] = $matches[0];
}
```

---

## 🚀 Advanced Features

### 1. **Question Type Awareness**
Bot adapts responses based on question type:
- "Hoeveel?" → Provides numbers/amounts
- "Hoe?" → Provides step-by-step instructions
- "Wat?" → Provides definitions/explanations

### 2. **Entity-Based Context**
Bot uses extracted entities to refine responses:
- Mentions €50 → Answers relate to budget constraints
- Mentions woensdag → Answers relate to weekly schedule
- Mentions 23:00 → Answers relate to night time rules

### 3. **Confidence Scoring**
Frontend can see how confident the bot is:
```javascript
{
  semantic_match: {
    matched_term: "salary",
    intent: "loon",
    confidence: 0.8  // 80% sure
  }
}
```

---

## 📈 What This Enables

✅ **Better Understanding** - Bot understands variations of questions
✅ **Smarter Responses** - Context-aware answers
✅ **Less Frustration** - Fewer "I don't understand" messages
✅ **Natural Conversation** - Feels more like talking to a person
✅ **Future Improvements** - Easy to add more NLP features

---

## 🧪 Testing the NLP Features

### In Browser Console (F12):
```javascript
// Test NLP extraction
const testMessage = "What's the salary for new employees?";
const entities = extractEntities(testMessage);
console.log(entities);

// Test semantic matching
const result = enhanceMessageWithNLP(testMessage);
console.log(result.semantic_match);
```

### Test Messages to Try:
1. "How much can I earn?" → Should understand salary question
2. "When are we cooking?" → Should understand fika
3. "What to do in emergency?" → Should understand bhv
4. "How do I get access?" → Should understand pasje
5. "Can't come to work tomorrow" → Should understand huisregels

---

## 🎓 NLP Technology Used

### **Compromise.js**
- **Purpose**: Natural Language Processing for English/Dutch
- **License**: MIT (Open source)
- **Size**: ~30KB (lightweight)
- **Features**:
  - Part-of-speech tagging (nouns, verbs, etc.)
  - Entity extraction (dates, numbers, people)
  - Word matching & similarity
  - Question detection

### **Custom Semantic Mappings**
- Curated list of topic-related keywords
- Maps user terminology to bot intents
- Configurable for different use cases

---

## 📝 Summary

TechnoBot's NLP features mean:
- 📚 Understands **semantic meaning**, not just keywords
- 🔢 Extracts **entities** (amounts, dates, times)
- 🎯 Uses **context** to give better answers
- 🗣️ Feels like talking to a **real person**
- 🚀 Much **smarter matching** than fuzzy search alone

All without need for external APIs! Everything runs locally in the browser. 🔥
