<?php
include 'main.py';

$destino = $_POST['destino'];
$acompañamiento = $_POST['acompañamiento'];
$presupuesto = $_POST['presupuesto'];

//Mostrar los valores de la encuesta
//echo $destino;
//echo $acompañamiento;
//echo $presupuesto;

$texto="Hola papus:";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- PyScript CSS -->
    <link rel="stylesheet" href="https://pyscript.net/latest/pyscript.css">

    <!-- PyScript JS -->
    <script defer src="https://pyscript.net/latest/pyscript.js"></script>

    <link rel="stylesheet" href="https://pyscript.net/releases/2024.9.1/core.css">
    <script type="module" src="https://pyscript.net/releases/2024.9.1/core.js"></script>

    <title>Document</title>
</head>
<body>
    <input type="hidden" value="<?php echo $destino; ?>" id="gustos">
    <input type="hidden" value="<?php echo $presupuesto; ?>" id="gustos">
    <input type="hidden" value="<?php echo $acompañamiento; ?>" id="gustos">

    <script type="py" src="./main.py" config="./pyscript.json"></script>

    <py-script>

        def obtencion_datos():
            # Obtener el valor del input usando Element
            gustos = Element("destino").value
            presupuesto = Element("presupuesto").value
            acompa = Element("acompañamiento").value
            print(gustos, acompa, presupuesto)
        obtencion_datos()

        import threading
        from transformers import pipeline
        import pandas as pd

        class Person:
            def __init__(self, name, last_name, age, budget, category, route):
                self.name = name
                self.last_name = last_name
                self.age = age
                self.budget = budget
                self.category = category
                self.route = route
                
            ''''def __repr__(self):
                {
                "Name": f"{self.name + " " + self.last_name}",
                "Age": f"{self.age}",
                "Budget": f"{self.budget}",
                "Category": f"{self.category}"
                    }'''
                
        class SortingHat:
            def __init__(self, results=[]):
                self.__results = results
                
            def add_consult(self, options, prompt):
                generator = pipeline('zero-shot-classification')
                response = generator(
                    f"{prompt}",
                    candidate_labels = options,
                    )

                self.__results.append(response['labels'][0])
            
            @property
            def sorting_hat(self):
                return pd.Series(self.__results).value_counts().idxmax()
            
            def recommendation(self, establishments, start_hour):
                recommendation = {f"{start_hour}:00-{(start_hour+2) % 24}:00": "Paseo Durango (sponsored)"}
                i = 2
                for establishment in establishments:
                    start_time = (start_hour + i) % 24
                    end_time = (start_hour + 2 + i) % 24
                    
                    # Ensure the time format is correct (e.g., 23:00-01:00)
                    if end_time <= start_time:
                        end_time += 24
                    
                    recommendation[f"{start_time}:00-{end_time % 24}:00"] = establishment
                    i += 2
                    
                self.route = recommendation

        sh = SortingHat()

        options = ["culture","nightlife","education","gastronomy"] # categories for the users

        def add_consult_thread(value):
            sh.add_consult(options, value)
            
        def create_person():
            
            ''' --------- Category selector --------'''
            
            name = input("What is your name? ")
            last_name = input("What is your last name? ")
            age = int(input("How old are you? "))
            age_classification = ""
            
            if age > 18 and age < 25:
                prompts_classification = {"Age": "Places to go for young people"}
            elif age >= 25 and age < 30:
                prompts_classification = {"Age": "Places to go for a young-adult"}
            elif age >= 30 and age < 40:
                prompts_classification = {"Age": "Places to go for an adult"}
            else:
                prompts_classification = {"Age": "Places to go for old people"}
                
            
            companions = input("With whome are you traveling? - [1] Family, [2] Couple, [3] Alone, [4] Kids, [5] Friends -> ")
            match companions:
                case '1':
                    prompts_classification['Companions'] = "Places to go with family"
                case '2':
                    prompts_classification['Companions'] = "Places to go in a romantic date"
                case '3':
                    prompts_classification['Companions'] = "Places to meet people"
                case '4':
                    prompts_classification['Companions'] = "Places where kids can learn"
                case '5':
                    prompts_classification['Companions'] = "Places to eat with friends"
                    
            preferences = input("What do you like the most? [1] Green areas [2] Quite places [3] Dancing and drinking [4] Trying new food [5] Learn new things -> ")
            match preferences:
                case '1':
                    prompts_classification['Preferences'] = "Parks or zoologics and nature places"
                case '2':
                    prompts_classification['Preferences'] = "Quite and relaxing places"
                case '3':
                    prompts_classification['Preferences'] = "Bars, night clubs and parties"
                case '4':
                    prompts_classification['Preferences'] = "Visiting restaurants"
                case '5':
                    prompts_classification['Preferences'] = "Libraries and conferences"
            
            budget = int(input("How much do you wanna spend (dollars)? "))
            
                
            ''' ------ Final result ------'''    
            
            threads = []
            
            for value in prompts_classification.values():
                thread = threading.Thread(target=add_consult_thread, args=(value,))
                threads.append(thread)
                thread.start()
                
            for thread in threads:
                thread.join()
            
            category = sh.sorting_hat
            
            #print(f"[+] Category -> {category}")
            
            # Instantiate the Person class
            person = Person(name, last_name, age, budget, category, route=[])
            
            
            ''' ------ Routes -------'''
            
            #route_classification = {}
            
            schedule = {}
            
            match person.category:
                case 'nightlife':
                    
                    start_hour = 17
                    
                    option = ["Night club", "Bar", "Disco", "Restaurant"]
                    cheap_chill_establishments = ["restaurant1", "restaurant3", "bar1"]
                    expensive_chill_establishments = ["restaurant2", "bar2"]
                    
                    cheap_dance_establishments = ["disco1", "night club 2"]
                    expensive_dance_establishments = ["night club 1", "disco2", "disco3"]
                    
                    dance = int(input("Do you like to dance? [1] Yes, [2] No -> "))
                    if dance == 1:
                        #route_classification['Dance'] = "Places to dance"
                        
                        if person.budget <= 250:
                            
                            schedule = sh.recommendation(cheap_dance_establishments, start_hour)
                        
                        else:
                            
                            schedule = sh.recommendation(expensive_dance_establishments, start_hour) 

                    else:
                        #route_classification['Dance'] = "Places to drink alcohol and chill"
                        if person.budget <= 250:

                            schedule = sh.recommendation(cheap_chill_establishments, start_hour)

                        else:
                            
                            schedule = sh.recommendation(expensive_chill_establishments, start_hour)    
                        
                case 'culture':
                    start_hour = 10
                    option = ["Museum", "Art Gallery", "Theater", "Outside trip"]
                    closed_places = ["museum1", "art gallery1", "theater1"]
                    open_places = ["park1", "historical site1"]
                    
                    kind = int(input("Do you prefer closed places or open places? [1] Closed, [2] Open -> "))
                    if kind == 1:
                        if person.budget <= 250:
                            schedule = sh.recommendation(closed_places, start_hour)
                        else:
                            schedule = sh.recommendation(closed_places, start_hour)
                    else:
                        if person.budget <= 250:
                            schedule = sh.recommendation(open_places, start_hour)
                        else:
                            schedule = sh.recommendation(open_places, start_hour)
                
                case 'gastronomy':
                    start_hour = 12
                    option = ["Restaurant", "Cafe", "Food Truck", "Market"]
                    cheap_places = ["cafe1", "food truck1", "market1"]
                    expensive_places = ["restaurant1", "restaurant2"]
                    
                    cuisine_preference = int(input("Do you prefer local or international cuisine? [1] Local, [2] International -> "))
                    if cuisine_preference == 1:
                        cheap_places = ["local cafe1", "local food truck1", "local market1"]
                        expensive_places = ["local restaurant1", "local restaurant2"]
                    else:
                        cheap_places = ["international cafe1", "international food truck1", "international market1"]
                        expensive_places = ["international restaurant1", "international restaurant2"]
                    
                    if person.budget <= 250:
                        schedule = sh.recommendation(cheap_places, start_hour)
                    else:
                        schedule = sh.recommendation(expensive_places, start_hour)
                
                case 'education':
                    start_hour = 9
                    option = ["Library", "Workshop", "Lecture", "Seminar"]
                    free_events = ["library1", "workshop1"]
                    paid_events = ["lecture1", "seminar1"]
                    
                    topic_preference = int(input("Do you prefer technical or non-technical topics? [1] Technical, [2] Non-technical -> "))
                    if topic_preference == 1:
                        free_events = ["tech library1", "tech workshop1"]
                        paid_events = ["tech lecture1", "tech seminar1"]
                    else:
                        free_events = ["non-tech library1", "non-tech workshop1"]
                        paid_events = ["non-tech lecture1", "non-tech seminar1"]
                    
                    if person.budget <= 250:
                        schedule = sh.recommendation(free_events, start_hour)
                    else:
                        schedule = sh.recommendation(paid_events, start_hour)
                        
            return Person(name, last_name, age, budget, category, schedule)

        if __name__ == '__main__':
            #users = []
            brute_data = []
            while True:
                end_program = int(input("[+] Add new user? \n\t[1] Yes\n\t[2] No\n\t-> "))
                if end_program == 1:
                    person = create_person()
                    brute_data.append({
                                    "Name": f"{person.name + " " + person.last_name}",
                                    "Age": f"{person.age}",
                                    "Budget": f"{person.budget}",
                                    "Category": f"{person.category}"
                                    })
                elif end_program == 2:
                    '''for user in users:
                        brute_data = {
                                    "Name": f"{user.name + " " + user.last_name}",
                                    "Age": f"{user.age}",
                                    "Budget": f"{user.budget}",
                                    "Category": f"{user.category}"
                                    }'''
                    print(f"[!] FINAL USERS ANALYSIS -> {brute_data}")
                    break
       
    </py-script>
</body>
</html>