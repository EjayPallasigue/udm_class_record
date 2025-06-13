<?php
header('Content-Type: application/json');

// Knowledge Base for the Chatbot
// You can expand this array with more questions and answers.
$knowledgeBase = [
    [
        'question' => "How do I create a new class?",
        'answer' => "To create a new class, click on 'Create New Class' in the sidebar menu. You will be prompted to enter the class name, section, and academic year."
    ],
    [
        'question' => "Can I make a class?",
        'answer' => "Yes, go to the sidebar and click 'Create New Class'. Fill in the required information like class name and section."
    ],
    [
        'question' => "How do I enroll students?",
        'answer' => "You can enroll students by clicking the 'Enroll' button next to the class in the 'Your Classes' table. You will then enter each student's details or upload a CSV file."
    ],
    [
        'question' => "Can I import student lists?",
        'answer' => "Yes, when enrolling students, you have the option to upload a CSV file containing student information."
    ],
    [
        'question' => "Where can I manage grade components?",
        'answer' => "Grade components can be managed by clicking the 'Components' button for a specific class. Here, you can add, edit, or delete components and set their weight."
    ],
    [
        'question' => "Can I change the weight of a component?",
        'answer' => "Yes, go to the 'Components' section of your class and edit the weights directly."
    ],
    [
        'question' => "How do I input grades?",
        'answer' => "To input grades, click the 'Grades' button for the relevant class. The interface will adapt based on whether you're using numerical or A/NA grading."
    ],
    [
        'question' => "Can I enter grades manually?",
        'answer' => "Yes, grades can be entered manually through the 'Grades' section."
    ],
    [
        'question' => "How do I view class records?",
        'answer' => "You can view class records by clicking the 'View' button next to your desired class. This will show student data, grades, and analytics."
    ],
    [
        'question' => "Where can I see all class data?",
        'answer' => "Click the 'View' button next to your class to access a complete overview including grades and student lists."
    ],
    [
        'question' => "How do I edit a class?",
        'answer' => "To edit class details, click the 'Edit' button next to the class. You can change the class name, section, and academic year."
    ],
    [
        'question' => "Can I rename a class?",
        'answer' => "Yes, use the 'Edit' button next to the class name to rename it."
    ],
    [
        'question' => "How do I delete a class?",
        'answer' => "To delete a class, click the 'Delete' button next to it. Be careful—this action is permanent and cannot be undone!"
    ],
    [
        'question' => "Can I recover a deleted class?",
        'answer' => "No, deleted classes are permanently removed. Be sure to back up your data before deleting."
    ],
    [
        'question' => "How can I save my database?",
        'answer' => "Click the 'Save Database' button to download a backup SQL file. <br><button type='button' class='btn btn-success mt-2' id='chatSaveDatabaseButton'><i class='bi bi-download'></i> Save Database Now</button>"
    ],
    [
        'question' => "How do I back up the system?",
        'answer' => "Use the 'Save Database' button to create a backup SQL file. You can also back it up to Google Drive."
    ],
    [
        'question' => "How do I import my database?",
        'answer' => "Click the 'Import Database' button, choose your backup SQL file, and confirm. Warning: this will overwrite existing data."
    ],
    [
        'question' => "How do I use Google Drive for database backup?",
        'answer' => "Install Google Drive for Desktop, create a folder named 'classrecorddb', and save your backups there. The files will sync automatically to your Google Drive."
    ],
    [
        'question' => "What is the grading system type A/NA?",
        'answer' => "A/NA-based grading means students receive either 'Approved' or 'Not Approved' statuses instead of numerical scores."
    ],
    [
        'question' => "What is numerical grading?",
        'answer' => "Numerical grading uses numeric scores for each assessment. The system calculates the final grade based on weighted components."
    ],
    [
        'question' => "Who created this system?",
        'answer' => "The UDM IntelliGrade system was created by Dr. Leila Gano and Engr. Jonathan De Leon."
    ],
    [
        'question' => "Who developed this system?",
        'answer' => "This system was developed by Erik Josef Pallasigue and Marvin Angelo Dela Cruz."
    ],
    [
        'question' => "What is the current academic year?",
        'answer' => "The academic year is displayed with your classes in the 'Your Classes' section."
    ],
    [
        'question' => "Can I customize grade components?",
        'answer' => "Yes, each class allows you to add or modify grading components using the 'Components' button."
    ],
    [
        'question' => "How can I backup my database?",
        'answer' => "Click the 'Save Database' button to download a backup SQL file. <br><button type='button' class='btn btn-success mt-2' id='chatSaveDatabaseButton'><i class='bi bi-download'></i> Save Database Now</button>"
    ],
    [
        'question' => "How can I backup my files?",
        'answer' => "Click the 'Save Database' button to download a backup SQL file. <br><button type='button' class='btn btn-success mt-2' id='chatSaveDatabaseButton'><i class='bi bi-download'></i> Save Database Now</button>"
    ],
    [
        'question' => "Hello Isla",
        'answer' => "Hi, I'm Isla, IntelliGrade System Lecturer's Assistant. How can I help you today?"
    ],
    [
        'question' => "Hi Isla",
        'answer' => "Hi, I'm Isla, IntelliGrade System Lecturer's Assistant. How can I help you today?"
    ],
    [
        'question' => "What is IntelliGrade?",
        'answer' => "IntelliGrade is a class record management system designed to help teachers manage classes, enroll students, and input grades efficiently."
    ],
    [
        'question' => "How to Clear Chat?",
        'answer' => "Just say, 'Clear chat' or 'Reset chat', and I will clear our conversation history."
    ],
    // New "What can you do?" question
    [
        'question' => "What can you do?",
        'answer' => "I am Isla, the IntelliGrade System Lecturer's Assistant. I can help you with:
        \n- Creating, editing, and deleting classes
        \n- Enrolling students and importing student lists
        \n- Managing grade components and their weights
        \n- Inputting and viewing grades (both numerical and A/NA)
        \n- Viewing class records and analytics
        \n- Backing up and importing your database
        \n- Setting up Google Drive for database backups
        \n- Taking and showing your notes"
    ],
    [
        'question' => "What are your capabilities?",
        'answer' => "I am Isla, the IntelliGrade System Lecturer's Assistant. I can help you with:
        \n- Creating, editing, and deleting classes
        \n- Enrolling students and importing student lists
        \n- Managing grade components and their weights
        \n- Inputting and viewing grades (both numerical and A/NA)
        \n- Viewing class records and analytics
        \n- Backing up and importing your database
        \n- Setting up Google Drive for database backups
        \n- Taking and showing your notes"
    ],
    [
        'question' => "Tell me what you can do.",
        'answer' => "I am Isla, the IntelliGrade System Lecturer's Assistant. I can help you with:
        \n- Creating, editing, and deleting classes
        \n- Enrolling students and importing student lists
        \n- Managing grade components and their weights
        \n- Inputting and viewing grades (both numerical and A/NA)
        \n- Viewing class records and analytics
        \n- Backing up and importing your database
        \n- Setting up Google Drive for database backups
        \n- Taking and showing your notes "
    ],
    [
        'question' => "Isla, note that",
        'answer' => "Please tell me what you want to note. For example: 'Isla, note that the meeting is on Friday.'"
    ],
    [
        'question' => "Isla note that",
        'answer' => "Please tell me what you'd like to note. For example: 'Isla note that project submission is next Monday.'"
    ],
    [
        'question' => "Note that",
        'answer' => "Please tell me what you want to note. For example: 'Note that my appointment is at 2 PM tomorrow.'"
    ],
    [
        'question' => "Isla, add a note",
        'answer' => "Sure! What would you like me to add? For example: 'Isla, add a note about the upcoming exam.'"
    ],
    [
        'question' => "Isla add a note",
        'answer' => "Please tell me the note you want to add. For example: 'Isla add a note: pick up groceries after work.'"
    ],
    [
        'question' => "Add a note",
        'answer' => "What would you like me to add? For example: 'Add a note about the dentist appointment at 10 AM.'"
    ],
    [
        'question' => "Isla, remember this",
        'answer' => "I'm ready. What should I remember? For example: 'Isla, remember this: submit the report by Friday.'"
    ],
    [
        'question' => "Isla remember this",
        'answer' => "Sure! Please tell me what to remember. For example: 'Isla remember this: call Mom tonight.'"
    ],
    [
        'question' => "Remember this",
        'answer' => "Got it. What should I remember? For example: 'Remember this: check the mail before leaving.'"
    ],
    [
        'question' => "Isla, take note of this",
        'answer' => "Okay, what should I take note of? For example: 'Isla, take note of this: meeting moved to Room B.'"
    ],
    [
        'question' => "Isla take note of this",
        'answer' => "Sure, what would you like me to note? For example: 'Isla take note of this: buy flowers on Sunday.'"
    ],
    [
        'question' => "Take note of this",
        'answer' => "Sure. What should I take note of? For example: 'Take note of this: lunch with Alex at noon.'"
    ],
    [
        'question' => "Isla, write this down",
        'answer' => "Ready to write! What's the note? For example: 'Isla, write this down: project deadline is next week.'"
    ],
    [
        'question' => "Isla write this down",
        'answer' => "Sure, what should I write down? For example: 'Isla write this down: get milk after work.'"
    ],
    [
        'question' => "Write this down",
        'answer' => "Okay. Please tell me what to write. For example: 'Write this down: check inventory tomorrow.'"
    ],
    [
        'question' => "Isla, jot this down",
        'answer' => "Sure! What do you want me to jot down? For example: 'Isla, jot this down: start research on Monday.'"
    ],
    [
        'question' => "Jot this down",
        'answer' => "Sure. What's the note? For example: 'Jot this down: review the contract before Friday.'"
    ],
    [
        'question' => "Isla, make a note",
        'answer' => "Of course! What should I note? For example: 'Isla, make a note: call the electrician at 4 PM.'"
    ],
    [
        'question' => "Make a note",
        'answer' => "Please tell me what you'd like to note. For example: 'Make a note: schedule eye exam next week.'"
    ],
    [
        'question' => "Isla, save this note",
        'answer' => "Sure! What's the note you'd like me to save? For example: 'Isla, save this note: finalize the agenda.'"
    ],
    [
        'question' => "Save this note",
        'answer' => "Alright. What's the note? For example: 'Save this note: lunch meeting at 1 PM.'"
    ],
    [
        'question' => "Isla, keep this in mind",
        'answer' => "Got it. What should I keep in mind? For example: 'Isla, keep this in mind: order supplies tomorrow.'"
    ],
    [
        'question' => "Keep this in mind",
        'answer' => "Sure. What's the reminder? For example: 'Keep this in mind: follow up on the email.'"
    ],
    [
        'question' => "Isla, please note",
        'answer' => "Absolutely! What should I note? For example: 'Isla, please note: team meeting at 9 AM.'"
    ],
    [
        'question' => "Please note",
        'answer' => "Sure. Please tell me the note. For example: 'Please note: new password is updated.'"
    ],
    [
        'question' => "Isla, could you remember",
        'answer' => "Yes! What should I remember? For example: 'Isla, could you remember: book the venue.'"
    ],
    [
        'question' => "Could you remember",
        'answer' => "Sure. What would you like me to remember? For example: 'Could you remember: call Sarah at 3 PM.'"
    ],
    [
        'question' => "Isla, take this down",
        'answer' => "I'm listening! What should I take down? For example: 'Isla, take this down: budget is finalized.'"
    ],
    [
        'question' => "Take this down",
        'answer' => "Okay. What do you want me to take down? For example: 'Take this down: new office layout.'"
    ],
    [
        'question' => "Isla, record this",
        'answer' => "Ready! What would you like me to record? For example: 'Isla, record this: plan trip for July.'"
    ],
    [
        'question' => "Record this",
        'answer' => "Of course. What should I record? For example: 'Record this: buy dog food tomorrow.'"
    ],
    [
        'question' => "Isla, mark this",
        'answer' => "What would you like me to mark? For example: 'Isla, mark this: today's session was productive.'"
    ],
    [
        'question' => "Mark this",
        'answer' => "Sure. What would you like me to mark? For example: 'Mark this: water bill due Friday.'"
    ],
    [
        'question' => "Isla, I want to remember",
        'answer' => "I'm here to help! What do you want to remember? For example: 'Isla, I want to remember: keys are in the drawer.'"
    ],
    [
        'question' => "I want to remember",
        'answer' => "Sure. What would you like me to remember? For example: 'I want to remember: check thermostat settings.'"
    ],
    [
        'question' => "Isla, log this",
        'answer' => "Sure. What should I log? For example: 'Isla, log this: expenses for the month are finalized.'"
    ],
    [
        'question' => "Log this",
        'answer' => "Okay. What would you like me to log? For example: 'Log this: completed module 3 today.'"
    ],
    [
        'question' => "Show me my notes",
        'answer' => "Here are your notes: " // This will be dynamically populated
    ],
    [
        'question' => "What notes do I have?",
        'answer' => "Here are your notes: " // This will be dynamically populated
    ],
    [
        'question' => "Can you show my notes?",
        'answer' => "Here are your notes: " // This will be dynamically populated
    ]

];

// Database connection (replace with your actual database credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "udm_class_record_db"; // Replace with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // If database connection fails, send an error response.
    echo json_encode(['response' => "Sorry, I'm having trouble connecting right now. Please try again later. (DB Connection Error)"]);
    exit(); // Stop execution if no DB connection
}

// Create notes table if it doesn't exist
$sql_create_table = "CREATE TABLE IF NOT EXISTS notes (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    note_content TEXT NOT NULL,
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if ($conn->query($sql_create_table) === TRUE) {
    // Table created successfully or already exists
} else {
    error_log("Error creating table: " . $conn->error);
}


// Get the user's query from the POST request
$userQuery = isset($_POST['query']) ? trim($_POST['query']) : '';
$botResponse = "I'm sorry, I don't understand that question. Please try rephrasing, or ask about creating classes, managing grades, or database backups."; // Default response

if (!empty($userQuery)) {
    $userQueryLower = strtolower($userQuery);
    $bestMatchScore = 0;
    $threshold = 60; // Percentage similarity threshold (adjust as needed)
    $matchedQuestion = ''; // Initialize matchedQuestion

    // Debugging: Log the incoming user query
    error_log("User Query: " . $userQuery);
    error_log("User Query Lower: " . $userQueryLower);

    // First, check for note-taking commands and extract the note
    $noteKeywords = ["isla, note that", "isla note that", "note that",
    "isla, add a note", "isla add a note", "add a note",
    "isla, remember this", "isla remember this", "remember this",
    "isla, take note of this", "isla take note of", "isla take note", "take note of this", "take note of",
    "isla, write this down", "isla write this down", "write this down",
    "isla, jot this down", "isla jot this down", "jot this down",
    "isla, make a note", "isla make a note", "make a note",
    "isla, save this note", "isla save this note", "save this note",
    "isla, keep this in mind", "isla keep this in mind", "keep this in mind",
    "isla, please note", "isla please note", "please note",
    "isla, could you remember", "isla could you remember", "could you remember",
    "isla, take this down", "isla take this down", "take this down",
    "isla, record this", "isla record this", "record this",
    "isla, mark this", "isla mark this", "mark this",
    "isla, I want to remember", "isla I want to remember", "I want to remember",
    "isla, log this", "isla log this", "log this",];
    $isNoteCommand = false;
    $noteContent = '';

    foreach ($noteKeywords as $keyword) {
        if (strpos($userQueryLower, $keyword) === 0) {
            $noteContent = trim(substr($userQuery, strlen($keyword)));
            $isNoteCommand = true;
            break;
        }
    }

    if ($isNoteCommand && !empty($noteContent)) {
        // Save the note to the database
        $stmt = $conn->prepare("INSERT INTO notes (note_content) VALUES (?)");
        $stmt->bind_param("s", $noteContent);
        if ($stmt->execute()) {
            $botResponse = "I've noted that: \"" . htmlspecialchars($noteContent) . "\" for you.";
        } else {
            $botResponse = "Sorry, I couldn't save the note due to a database error.";
            error_log("Error saving note: " . $stmt->error);
        }
        $stmt->close();
    } else {
        // Proceed with regular knowledge base lookup if not a note-adding command
        foreach ($knowledgeBase as $item) {
            $questionLower = strtolower($item['question']);

            // Calculate similarity using similar_text for fuzzy matching
            similar_text($userQueryLower, $questionLower, $percent);

            // Debugging: Log similarity scores for each knowledge base question
            error_log("Comparing '" . $userQueryLower . "' with '" . $questionLower . "': " . $percent . "% similarity.");

            if ($percent > $bestMatchScore && $percent >= $threshold) {
                $bestMatchScore = $percent;
                $botResponse = $item['answer'];
                $matchedQuestion = $item['question']; // Store the matched question
                error_log("Best match updated: '" . $matchedQuestion . "' with " . $bestMatchScore . "%");
            }
        }

        // Debugging: Log the final best match and score
        error_log("Final Best Match: '" . $matchedQuestion . "' with " . $bestMatchScore . "%");
        error_log("Threshold: " . $threshold);

        // If a "show notes" query was matched, fetch and display notes
        if (strpos($matchedQuestion, "Show me my notes") !== false ||
            strpos($matchedQuestion, "What notes do I have?") !== false ||
            strpos($matchedQuestion, "Can you show my notes?") !== false) {

            error_log("Attempting to fetch notes from the database.");

            $sql = "SELECT note_content FROM notes ORDER BY reg_date DESC";
            $result = $conn->query($sql);

            if ($result === FALSE) {
                error_log("SQL SELECT Error: " . $conn->error);
                $botResponse = "Sorry, I encountered an error while trying to retrieve your notes from the database. (SQL Error)";
            } elseif ($result->num_rows > 0) {
                $notesText = "Here are your notes:\n\n"; // Initial text, use \n for new lines
                $counter = 1; // Initialize counter for numbering
                while($row = $result->fetch_assoc()) {
                    // Append each note with a number, a period, a space, the content, and a newline character
                    $notesText .= $counter . ". " . htmlspecialchars($row["note_content"]) . "\n";
                    $counter++; // Increment counter
                }
                $botResponse = $notesText;
                error_log("Notes fetched successfully (plain text numbered).");
            } else {
                $botResponse = "You don't have any notes yet.";
                error_log("No notes found in the database.");
            }
        }
    }
}

$conn->close();
echo json_encode(['response' => $botResponse]);
?>