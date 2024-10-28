## LMS
This is a  learning management system using the Laravel framework. It is designed to capture data from training simulations running in a separate Unity application and store the data for later review by students and instructors. The site has a lot of other features, including a scenario creator that instructors can use to create custom training simulations. Included here are a few samples of site features I worked on. It is not complete, and some functions may be stubs only.

Some items included in the sample:
- Aclamate API wrapper - Intended for a client app running in Unity that would hit this endpoint. The web API would request the data from Aclamate, store it to the database, and then return it to the client. There were also a few other features, like recieving event info from the client and sending it to the Aclamate server for use on their end.

  * Related classes: Backend models, repos, controllers called Aclamate and Data

- Scenario creation wizard - Included are the first couple of steps of the wizard, including server-side and client-side code. Uses Bootstrap for layout and modals. Uses jQuery AJAX. Voice sample playback for character select is also included. On the server, we used a third-party speech to text generater (not included here).
  * public/js/voice_sample.js
  * resources/views/backend/scenarios/conversationEditor/*

- Student course access using Eloquent many-to-many feature. Students request access to a course by visiting a link. Instructor chooses to allow or deny access. Student would see a list of approved courses in their user dashboard. Lists of courses are generated using Datatables for AJAX loading, pagination, and filtering.
  * app/Models/Courses/*
  * app/Models/Auth/*
  * app/Http/Controllers/Frontend/User/*
  * app/Repositories/Backend/Scenarios/*
