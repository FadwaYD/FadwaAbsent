<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stagiaire Table with Sidebar and Search</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- SheetJS Library for Excel Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

    <script>
        const data = [];
        const originalData = []; // Déclaration de originalData

        window.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll("#table-body tr").forEach(row => {
                const rowData = [];
                const rowLinks = { delete: '', edit: '' }; // Initialisation explicite

                row.querySelectorAll("td").forEach((cell, index) => {
                    if (index < 7) { // Pour les colonnes des données
                        rowData.push(cell.innerText);
                    } else if (index === 7) { // Lien de suppression
                        rowLinks.delete = cell.innerHTML; 
                    } else if (index === 8) { // Lien de modification
                        rowLinks.edit = cell.innerHTML;
                    }
                });

                originalData.push({ data: rowData, links: rowLinks }); // Ajout à originalData
            });

            console.log(originalData); // Vérifiez ici pour le débogage
        });

        function filterTable() {
            const input = document.getElementById("searchInput").value.toLowerCase();
            const tableBody = document.getElementById("table-body");

            tableBody.innerHTML = ""; // Efface le contenu du corps du tableau

            originalData.forEach(row => {
                const match = row.data.some(cellData => cellData.toLowerCase().includes(input));
                if (match) {
                    const rowElement = document.createElement("tr");
                    rowElement.classList.add("hover:bg-gray-100");
                    
                    // Ajoutez d'abord les cellules de données
                    row.data.forEach(cellData => {
                        const cell = document.createElement("td");
                        cell.className = 'py-2 px-4 border-b';
                        cell.innerText = cellData; // Ajoute les données aux cellules
                        rowElement.appendChild(cell);
                    });

                    // Ajoutez les liens à la fin
                    const deleteCell = document.createElement("td");
                    deleteCell.className = 'py-2 px-4 border-b';
                    deleteCell.innerHTML = row.links.delete; // Lien de suppression
                    rowElement.appendChild(deleteCell);

                    const editCell = document.createElement("td");
                    editCell.className = 'py-2 px-4 border-b';
                    editCell.innerHTML = row.links.edit; // Lien de modification
                    rowElement.appendChild(editCell);

                    tableBody.appendChild(rowElement);
                }
            });
        }

        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("hidden");
        }

        // Export to Excel function
        function exportToExcel() {
            const table = document.getElementById("data-table");
            const worksheet = XLSX.utils.table_to_sheet(table);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "Stagiaires");

            XLSX.writeFile(workbook, "stagiaires.xlsx");
        }
    </script>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar with Navy Blue Background -->
        <aside id="sidebar" class="w-64 bg-blue-900 shadow-md hidden md:block">
            <div class="p-6">
                <img src="https://th.bing.com/th/id/R.b6fae5d9e78a1e2dfe1059c9db1a68aa?rik=zvz3oE9xuEBxVQ&riu=http%3a%2f%2ftous-logos.com%2fwp-content%2fuploads%2f2019%2f03%2fLogo-OFPPT.png&ehk=n4bjmSAkWEmjJcCrgAFqAjxagn2Sypl4RqRrYYgr9gw%3d&risl=&pid=ImgRaw&r=0" alt="Logo" class="w-24 h-auto mx-auto mb-4">
                <nav class="mt-6">
                    <ul>
                        <li class="my-2">
                            <a href="opp.php" class="flex items-center p-2 text-white hover:bg-blue-800 rounded">
                                <i class="fas fa-home mr-3"></i>Operation
                            </a>
                        </li>
                        <li class="my-2">
                            <a href="justification.php" class="flex items-center p-2 text-white hover:bg-blue-800 rounded">
                                <i class="fas fa-check-circle mr-3"></i>Justifier les absences
                            </a>
                        </li>
                        <li class="my-2">
                            <a href="formateures_login.php" class="flex items-center p-2 text-white hover:bg-blue-800 rounded">
                                <i class="fas fa-gavel mr-3"></i>Enregister les absences
                            </a>
                        </li>
                      
                        <li class="my-2">
                            <a href="statistique_Groupe.php" class="flex items-center p-2 text-white hover:bg-blue-800 rounded">
                                <i class="fas fa-chart-pie mr-3"></i>Les Statistique par group
                            </a>
                        </li>
                        <li class="my-2">
                            <a href="discipline.php" class="flex items-center p-2 text-white hover:bg-blue-800 rounded">
                                <i class="fas fa-gavel mr-3"></i>Discipline
                            </a>
                        </li>
                        <li class="my-2">
                            <a href="logout.php" class="flex items-center p-2 text-white hover:bg-blue-800 rounded">
                                <i class="fas fa-sign-out-alt mr-3"></i>Deconnecter
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 p-6">
            <button onclick="toggleSidebar()" class="md:hidden bg-blue-500 text-white p-2 rounded mb-4">
                Toggle Sidebar
            </button>
            
            <h1 class="text-2xl font-bold text-gray-700 mb-4">Stagiaire Table</h1>

            <!-- Search Bar, Search Button, and Export Button -->
            <div class="mb-4 flex items-center gap-2">
                <input
                    type="text"
                    id="searchInput"
                    placeholder="Search..."
                    class="px-4 py-2 border border-gray-300 rounded w-full md:w-1/2"
                />
                <button onclick="filterTable()" class="px-4 py-2 bg-blue-500 text-white rounded">
                    Search
                </button>
                <button onclick="exportToExcel()" class="px-4 py-2 bg-green-500 text-white rounded">
                    Export to Excel
                </button>
            </div>

            <!-- Responsive Table -->
            <div class="overflow-x-auto">
                <table id="data-table" class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b">Matricule</th>
                            <th class="py-2 px-4 border-b">Nom</th>
                            <th class="py-2 px-4 border-b">Prénom</th>
                            <th class="py-2 px-4 border-b">Note Discipline</th>
                            <th class="py-2 px-4 border-b">Nom Groupe</th>
                            <th class="py-2 px-4 border-b">Nom Filière</th>
                            <th class="py-2 px-4 border-b">Email</th>
                            <th class="py-2 px-4 border-b">Modifier</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <?php
                        require_once "connexion.php"; 

                        try {
                            $query = "SELECT st.id, st.matricul AS matricul, st.nom AS nom, st.prenom AS prenom, st.note_disipline AS note_disipline, 
                                      gr.nom_group AS nom_group, fi.nom_filier AS nom_filier, ut.email AS email
                                      FROM utilisateur ut 
                                      JOIN stagiaires st ON st.matricul = ut.matricul 
                                      JOIN groupes gr ON gr.id = st.group_id
                                      JOIN filieres fi ON fi.id = gr.filiere_id";

                            $stmt = $pdo->query($query);
                            $stagaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($stagaires as $stagiaire) {
                                echo "<tr>";
                                echo "<td class='py-2 px-4 border-b'>{$stagiaire['matricul']}</td>";
                                echo "<td class='py-2 px-4 border-b'>{$stagiaire['nom']}</td>";
                                echo "<td class='py-2 px-4 border-b'>{$stagiaire['prenom']}</td>";
                                echo "<td class='py-2 px-4 border-b'>{$stagiaire['note_disipline']}</td>";
                                echo "<td class='py-2 px-4 border-b'>{$stagiaire['nom_group']}</td>";
                                echo "<td class='py-2 px-4 border-b'>{$stagiaire['nom_filier']}</td>";
                                echo "<td class='py-2 px-4 border-b'>{$stagiaire['email']}</td>";
                                echo "<td class='py-2 px-4 border-b'><a href='modifier stagiaire.php?id={$stagiaire['id']}' class='text-blue-500'>Modifier</a></td>";
                                echo "</tr>";
                            }
                        } catch (PDOException $e) {
                            echo "Error: " . $e->getMessage();
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
