<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PopDropkick API</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-b from-gray-100 to-gray-200 flex flex-col items-center p-6 font-sans">

<h1 class="text-5xl font-extrabold mb-4 text-gray-800">PopDropkick API <i class="fas fa-fire text-red-500"></i></h1>
<p class="text-gray-600 mb-10 text-center max-w-xl">Pro wrestling title history & rosters API. Explore champions, reigns, and more!</p>

<!-- API Examples -->
<div class="grid md:grid-cols-2 gap-8 max-w-6xl w-full mb-12">
    <div class="bg-white shadow-xl p-6 rounded-3xl hover:shadow-2xl transition-shadow duration-300">
        <h2 class="font-semibold text-lg mb-2 flex items-center"><i class="fas fa-terminal mr-2 text-green-500"></i>Example Request</h2>
        <pre class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm overflow-x-auto">GET /api/wrestlers/finn-balor</pre>
        <h2 class="font-semibold mt-4 mb-2 flex items-center"><i class="fas fa-database mr-2 text-blue-400"></i>Response</h2>
        <pre class="bg-gray-900 text-blue-400 p-4 rounded-lg text-sm overflow-x-auto">{
  "id": 1,
  "name": "Finn Bálor",
  "promotion": "WWE (NXT)",
  "title_reigns": [
    { "championship": "NXT Championship", "reigns": 2 }
  ]
}</pre>
    </div>

    <div class="bg-white shadow-xl p-6 rounded-3xl hover:shadow-2xl transition-shadow duration-300">
        <h2 class="font-semibold text-lg mb-2 flex items-center"><i class="fas fa-terminal mr-2 text-green-500"></i>Example Request</h2>
        <pre class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm overflow-x-auto">GET /api/championships/nxt-championship</pre>
        <h2 class="font-semibold mt-4 mb-2 flex items-center"><i class="fas fa-database mr-2 text-blue-400"></i>Response</h2>
        <pre class="bg-gray-900 text-blue-400 p-4 rounded-lg text-sm overflow-x-auto">{
  "id": 1,
  "name": "NXT Championship",
  "current_holder": "Ilja Dragunov",
  "reigns": 17
}</pre>
    </div>
</div>

<!-- Contact Info -->
<div class="bg-white shadow-xl p-6 rounded-3xl max-w-md w-full mb-12 hover:shadow-2xl transition-shadow duration-300">
    <h2 class="text-2xl font-semibold mb-4 flex items-center"><i class="fas fa-envelope mr-2 text-blue-500"></i>Contact</h2>
    <form action="https://formsubmit.co/b65c504ec54c30d40486bf377a6e5d1b" method="POST" class="space-y-4">
        <input type="text" name="name" placeholder="Name" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
        <input type="email" name="email" placeholder="Email" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
        <textarea name="message" placeholder="Message" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" rows="4" required></textarea>
        <input type="hidden" name="_next" value="/thank-you">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg shadow-lg transition-colors duration-300 w-full flex items-center justify-center">
            <i class="fas fa-paper-plane mr-2"></i>Send Message
        </button>
    </form>
    <p class="mt-4 text-gray-500 text-sm">Or open an issue on GitHub:</p>
    <a href="https://github.com/outofjam/popdropkickv1/issues" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline flex items-center mt-1"><i class="fab fa-github mr-2"></i>Submit an issue</a>
</div>

<!-- GitHub Link -->
<a href="https://github.com/outofjam/popdropkickv1" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline text-lg flex items-center mb-8"><i class="fab fa-github mr-2"></i>View on GitHub</a>

</body>
</html>
