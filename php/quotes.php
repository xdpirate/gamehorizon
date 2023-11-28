<?php
$quotes = [
    ["This game sucks", "AVGN"],
    ["Die monster. You don't belong in this world!", "Richter Belmont"],
    ["What is a man? A miserable little pile of secrets", "Dracula"],
    ["HYAUGH!", "Link"],
    ["Don't look so smug! I know what you're thinking, but Tempest Keep was merely a set back", "Kael'thas Sunstrider"],
    ["Suffocate upon your own hate!", "Sara"],
    ["Tremble, mortals, before the coming of the end!", "Sara"],
    ["Your friends will abandon you", "C'Thun"],
    ["Your heart will explode", "C'Thun"],
    ["It's a me, Mario!", "Mario"],
    ["Wahoo!", "Mario"],
    ["Wah!", "Waluigi"],
    ["Pikachu!", "Pikachu"],
    ["Pika pi!", "Pikachu"],
    ["Hi! I like shorts! They're comfy and easy to wear!", "Youngster"],
    ["Forest just setback!", "Hogger"],
    ["Somebody set up us the bomb", "Mechanic"],
    ["All your base are belong to us", "CATS"],
    ["You have no chance to survive make your time", "CATS"],
    ["You attack its weak point for massive damage", "Bill Ritch"],
    ["Do a barrel roll!", "Peppy"],
    ["If you keep going the way you are now... you're gonna have a bad time", "Sans"],
    ["You put the wit in twit, sir", "Lawrence"],
    ["I shall let you live, little alien... Psych! Mr. Zurkon lives only to kill", "Mr. Zurkon"],
    ["Mr. Zurkon conducts a symphony of pain", "Mr. Zurkon"],
    ["You are a disease, and Mr. Zurkon is the cure!", "Mr. Zurkon"],
];

$pick = rand(0,sizeof($quotes)-1);
print("<i>\"" . $quotes[$pick][0] . "\"</i> - " . $quotes[$pick][1]);
?>
