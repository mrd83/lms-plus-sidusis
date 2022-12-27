# lms-sidusis_update_netranges - script for Lan Management System (LMS) 
########################## readme EN (PL below):

Requirements (will probably run on earlier versions, but tested on the following):
"php" : ">=7.4.30",
"chilek/lms-plus" : ">=27.52"

Script to automatically create/update netranges table entries for lms-plus NMS

*by "technology stack" we understand a SINGLE group of variables required for netranges table:
['linktype', 'linktechnology', 'downlink', 'uplink', 'type', 'services']

How to use (tl;dr - brief version):
1. Modify the '$node_names' array in the "user variables" section (first lines of the script) according to your needs:
   - I intentionally left my values as an example,
   - possible values for "technology stack" are listed in the comment under '$node_names' variable,
2. Change the value of '$i_updated_my_vars' to true,
3. Run the script without parameters. RUNNING IT WITHOUT PARAMETERS IS SAFE! It won't write anything to DB!
4. Read the help (-h parameter) and run the script with desired parameter.

How to use (full: usage and operation):
1. Modify the '$node_names' array in the "user variables" section, then change the value of '$i_updated_my_vars' to true.
2. The script uses the names assigned to devices in the LMS system to distinguish which "technology stack" is to be used 
    and where. For example: if all your switches are named (as in my own LMS) "SW-<some_numbers>" AND you want to assign 
    them all the same "technology stack(s)" - this script is for you! 
    This script WILL NOT automatically assign a "technology stack" to all your devices (it's planned but not yet implemented). 
3. After adding the chosen devices group as a user variable (along with "technology stack"), the script looks for 
    the selected devices (in the LMS DB "nodes" table), the addresses assigned to them (in the "addresses" table), 
    and finally for the building id exactly matching the address (in the "location_buildings" table). Without the '-m' 
    parameter, it logs each step to files (created in the directory from which the script was run), explaining all 
    inconsistencies for each device group in the "problematic_devices" log. It's all done in "MAIN" by calling 
    the 'get_building_ids_for_node_type' function, which returns an array of UNIQUE building id's for the user-selected 
    node name. Then, for each building id, the script creates entries in '$candidates_array' with all the technology 
    stacks previously selected as a user variable. 
4. After processing ALL selected device groups, the script performs an additional check of '$candidates_array' 
    to eliminate possible duplicates. 
5. If it was run without parameters - it'll exit now, leaving logs and some console output.
6. To actually use this script, select the parameter. Run with the '-h' parameter for a detailed description.


########################## readme PL:

Wymagania (prawdopodobnie będzie działać na wcześniejszych wersjach, ale testowany na poniższych):
"php" : ">=7.4.30",
"chilek/lms-plus" : ">=27.52"

Skrypt do automatycznego tworzenia/aktualizacji tabeli netranges dla lms-plus

*przez "stos technologii (technology stack)" rozumiemy JEDNĄ grupę zmiennych wymaganych dla tabeli netranges:
['linktype', 'linktechnology', 'downlink', 'uplink', 'type', 'services']

Sposób użycia (tl;dr - wersja skrócona):
1. Zmodyfikuj tablicę '$node_names' w sekcji "zmienne użytkownika" (pierwsze linie skryptu) wedle swoich potrzeb:
    - celowo zostawiłem swoje wartości jako przykład,
    - możliwe wartości dla "stosu technologii" są wymienione w komentarzu poniżej zmiennej '$node_names',
2. Zmień wartość '$i_updated_my_vars' na true,
3. Uruchom skrypt bez parametrów. URUCHOMIENIE GO BEZ PARAMETRÓW JEST BEZPIECZNE! Nie zapisze on żadnych zmian do bazy!
4. Przeczytaj pomoc (parametr -h) i uruchom skrypt z żądanym parametrem.

Sposób użycia (wersja pełna: użycie i działanie):
1. Zmodyfikuj tablicę '$node_names' w sekcji "zmienne użytkownika", następnie zmień wartość '$i_updated_my_vars' na true.
2. Skrypt wykorzystuje nazwy przypisane do urządzeń w systemie LMS, aby rozróżnić który "stos technologii" ma być użyty 
    i gdzie. Na przykład: jeśli wszystkie Twoje przełączniki mają nazwę (jak w moim LMS-ie) "SW-<jakieś_liczby>" ORAZ 
    chcesz przypisać im wszystkim ten sam "stos technologii" (bądź kilka na raz - patrz przykład dla 'SW-%') - ten 
    skrypt jest dla Ciebie!
    UWAGA: skrypt NIE BĘDZIE automatycznie przypisywał "stosu technologii" do Twoich urządzeń (jest to planowane, 
    ale jeszcze nie wdrożone). 
3. Po dodaniu nazwy wybranej grupy urządzeń jako zmiennej użytkownika (wraz ze "stosem technologii"), skrypt szuka 
    wybranych urządzeń (w tabeli "nodes" LMS-a), przypisanych im adresów (w tabeli "addresses") i wreszcie id budynku 
    dokładnie odpowiadającego adresowi (w tabeli "location_buildings"). Bez parametru "-m" loguje każdy krok do 
    plików (tworzonych w katalogu, z którego uruchomiono skrypt), starając się wyjaśnić niespójności dla każdej grupy 
    urządzeń w logu "problematic_devices". Wszystko odbywa się w "MAIN" poprzez wywołanie funkcji 'get_building_ids_for_node_type', 
    która zwraca tablicę UNIKALNYCH identyfikatorów budynków dla wybranej przez użytkownika nazwy węzła. Następnie dla 
    każdego id budynku skrypt tworzy wpisy w tablicy '$candidates_array' z wszystkimi "stosami technologii" wybranymi 
    wcześniej jako zmienna użytkownika. 
4. Po przetworzeniu WSZYSTKICH wybranych grup urządzeń skrypt wykonuje dodatkowe sprawdzenie tablicy '$candidates_array' 
    w celu wyeliminowania ewentualnych duplikatów. 
5. Jeśli został uruchomiony bez parametrów - zakończy się w tym momencie, pozostawiając logi i wyjście konsoli.
6. Aby faktycznie użyć tego skryptu, wybierz parametr. Uruchom z parametrem '-h', aby uzyskać szczegółowy opis.