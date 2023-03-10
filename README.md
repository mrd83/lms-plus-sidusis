# lms-sidusis_update_netranges - script for Lan Management System (LMS) 
########################## readme EN (PL below):  
Script to automatically create/update netranges table entries for lms-plus NMS

Requirements (will probably run on earlier versions, but tested on the following):  
"php" : ">=7.4.30",  
"chilek/lms-plus" : ">=27.52"

note: by "technology stack" we understand a SINGLE group of variables required for netranges table:  
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
Skrypt do automatycznego tworzenia/aktualizacji tabeli netranges dla lms-plus

Wymagania (prawdopodobnie b??dzie dzia??a?? na wcze??niejszych wersjach, ale testowany na poni??szych):  
"php" : ">=7.4.30",  
"chilek/lms-plus" : ">=27.52"

uwaga: przez "stos technologii (technology stack)" rozumiemy JEDN?? grup?? zmiennych wymaganych dla tabeli netranges:  
['linktype', 'linktechnology', 'downlink', 'uplink', 'type', 'services']

Spos??b u??ycia (tl;dr - wersja skr??cona):
1. Zmodyfikuj tablic?? '$node_names' w sekcji "zmienne u??ytkownika" (pierwsze linie skryptu) wedle swoich potrzeb:
    - celowo zostawi??em swoje warto??ci jako przyk??ad,
    - mo??liwe warto??ci dla "stosu technologii" s?? wymienione w komentarzu poni??ej zmiennej '$node_names',
2. Zmie?? warto???? '$i_updated_my_vars' na true,
3. Uruchom skrypt bez parametr??w. URUCHOMIENIE GO BEZ PARAMETR??W JEST BEZPIECZNE! Nie zapisze on ??adnych zmian do bazy!
4. Przeczytaj pomoc (parametr -h) i uruchom skrypt z ????danym parametrem.

Spos??b u??ycia (wersja pe??na: u??ycie i dzia??anie):
1. Zmodyfikuj tablic?? '$node_names' w sekcji "zmienne u??ytkownika", nast??pnie zmie?? warto???? '$i_updated_my_vars' na true.
2. Skrypt wykorzystuje nazwy przypisane do urz??dze?? w systemie LMS, aby rozr????ni?? kt??ry "stos technologii" ma by?? u??yty 
    i gdzie. Na przyk??ad: je??li wszystkie Twoje prze????czniki maj?? nazw?? (jak w moim LMS-ie) "SW-<jakie??_liczby>" ORAZ 
    chcesz przypisa?? im wszystkim ten sam "stos technologii" (b??d?? kilka na raz - patrz przyk??ad dla 'SW-%') - ten 
    skrypt jest dla Ciebie!
    UWAGA: skrypt NIE B??DZIE automatycznie przypisywa?? "stosu technologii" do Twoich urz??dze?? (jest to planowane, 
    ale jeszcze nie wdro??one). 
3. Po dodaniu nazwy wybranej grupy urz??dze?? jako zmiennej u??ytkownika (wraz ze "stosem technologii"), skrypt szuka 
    wybranych urz??dze?? (w tabeli "nodes" LMS-a), przypisanych im adres??w (w tabeli "addresses") i wreszcie id budynku 
    dok??adnie odpowiadaj??cego adresowi (w tabeli "location_buildings"). Bez parametru "-m" loguje ka??dy krok do 
    plik??w (tworzonych w katalogu, z kt??rego uruchomiono skrypt), staraj??c si?? wyja??ni?? niesp??jno??ci dla ka??dej grupy 
    urz??dze?? w logu "problematic_devices". Wszystko odbywa si?? w "MAIN" poprzez wywo??anie funkcji 'get_building_ids_for_node_type', 
    kt??ra zwraca tablic?? UNIKALNYCH identyfikator??w budynk??w dla wybranej przez u??ytkownika nazwy w??z??a. Nast??pnie dla 
    ka??dego id budynku skrypt tworzy wpisy w tablicy '$candidates_array' z wszystkimi "stosami technologii" wybranymi 
    wcze??niej jako zmienna u??ytkownika. 
4. Po przetworzeniu WSZYSTKICH wybranych grup urz??dze?? skrypt wykonuje dodatkowe sprawdzenie tablicy '$candidates_array' 
    w celu wyeliminowania ewentualnych duplikat??w. 
5. Je??li zosta?? uruchomiony bez parametr??w - zako??czy si?? w tym momencie, pozostawiaj??c logi i wyj??cie konsoli.
6. Aby faktycznie u??y?? tego skryptu, wybierz parametr. Uruchom z parametrem '-h', aby uzyska?? szczeg????owy opis.