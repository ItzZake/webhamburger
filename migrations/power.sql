CREATE TABLE UserProfile
(
  User_ID INT AUTO_INCREMENT,
  Email VARCHAR(255) NOT NULL,
  Password VARCHAR(255) NOT NULL,
  Last_Login DATE NOT NULL,
  First_Name VARCHAR(50) NOT NULL,
  Last_Name VARCHAR(50) NOT NULL,
  Phone_Number INT NOT NULL,
  DOB DATE NOT NULL,
  Role VARCHAR(50) NOT NULL,
  Gender VARCHAR(50) NOT NULL,
  Is_Active INT NOT NULL,
  Profile_pic_url VARCHAR(200) NOT NULL,
  Created_at DATETIME NOT NULL,
  Updated_at DATETIME NOT NULL,
  PRIMARY KEY (User_ID)
);

CREATE TABLE MemberProfile
(
  Member_Id INT NOT NULL,
  Em_Contact_Num INT NOT NULL,
  EM_Contact_Name VARCHAR(50) NOT NULL,
  Body_fat FLOAT NOT NULL,
  Height INT NOT NULL,
  Weight FLOAT NOT NULL,
  BMI FLOAT NOT NULL,
  Experience_Level VARCHAR(100) NOT NULL,
  Training_Goals VARCHAR(500) NOT NULL,
  Injuries VARCHAR(500) NOT NULL,
  Medical_Condition VARCHAR(500) NOT NULL,
  Created_at DATETIME NOT NULL,
  Updated_at DATETIME NOT NULL,
  PRIMARY KEY (Member_Id),
  FOREIGN KEY (Member_Id) REFERENCES UserProfile(User_ID)
);

CREATE TABLE CoachProfile
(
  Coach_ID INT NOT NULL,
  Bio VARCHAR(500) NOT NULL,
  Certifications VARCHAR(500) NOT NULL,
  rating_count FLOAT NOT NULL,
  Avg_rating INT NOT NULL,
  Is_Accepting_new INT NOT NULL,
  Max_Clients INT NOT NULL,
  Specialization_Main VARCHAR(100) NOT NULL,
  Specialization_Other VARCHAR(100) NOT NULL,
  Youtube_Url VARCHAR(500) NOT NULL,
  Instagram_Url VARCHAR(500) NOT NULL,
  Created_At DATETIME NOT NULL,
  Updated_At DATETIME NOT NULL,
  PRIMARY KEY (Coach_ID),
  FOREIGN KEY (Coach_ID) REFERENCES UserProfile(User_ID)
);

CREATE TABLE NutritionistProfile
(
  Nutritionist_ID INT NOT NULL,
  Licence_Number INT NOT NULL,
  Bio VARCHAR(500) NOT NULL,
  Certifications VARCHAR(500) NOT NULL,
  rating_count FLOAT NOT NULL,
  Avg_rating FLOAT NOT NULL,
  Is_accepting_new INT NOT NULL,
  Years_Experience INT NOT NULL,
  Specialization_Main VARCHAR(500) NOT NULL,
  Clinic_Location VARCHAR(100) NOT NULL,
  Updated_at DATETIME NOT NULL,
  Created_at DATETIME NOT NULL,
  PRIMARY KEY (Nutritionist_ID),
  FOREIGN KEY (Nutritionist_ID) REFERENCES UserProfile(User_ID)
);

CREATE TABLE AdminProfile
(
  Admin_ID INT NOT NULL,
  Job_Title VARCHAR(50) NOT NULL,
  Can_Manage_Users INT NOT NULL,
  Can_Manage_Memberships INT NOT NULL,
  Can_Manage_Store INT NOT NULL,
  Can_Manage_Nutritionists INT NOT NULL,
  Can_Manage_Coaches INT NOT NULL,
  Can_Manage_Appointments INT NOT NULL,
  Can_View_Reports INT NOT NULL,
  Created_at DATETIME NOT NULL,
  Updated_at DATETIME NOT NULL,
  PRIMARY KEY (Admin_ID),
  FOREIGN KEY (Admin_ID) REFERENCES UserProfile(User_ID)
);

CREATE TABLE AdminActionLog
(
  Log_ID INT NOT NULL,
  Target_Entity_Type VARCHAR(50) NOT NULL,
  Description VARCHAR(500) NOT NULL,
  Action_Type VARCHAR(500) NOT NULL,
  Created_at DATETIME NOT NULL,
  Admin_ID INT NOT NULL,
  PRIMARY KEY (Log_ID),
  FOREIGN KEY (Admin_ID) REFERENCES AdminProfile(Admin_ID)
);

CREATE TABLE MealPlan
(
  Meal_Plan_ID INT NOT NULL AUTO_INCREMENT,
  Title VARCHAR(255) NOT NULL,
  Description TEXT NOT NULL,
  Total_daily_Calories INT NOT NULL,
  Carbs_grams_per_day INT NOT NULL,
  Protein_grams_per_day INT NOT NULL,
  Fats_grams_per_day INT NOT NULL,
  Status VARCHAR(50) NOT NULL,
  Start_Date DATE NOT NULL,
  End_Date DATE NOT NULL,
  Created_at DATETIME NOT NULL,
  Updated_at DATETIME NOT NULL,
  Nutritionist_ID INT NOT NULL,
  Member_Id INT NOT NULL,
  PRIMARY KEY (Meal_Plan_ID),
  FOREIGN KEY (Nutritionist_ID) REFERENCES NutritionistProfile(Nutritionist_ID),
  FOREIGN KEY (Member_Id) REFERENCES MemberProfile(Member_Id)
);

CREATE TABLE WorkoutProgram
(
  Workout_ID INT NOT NULL,
  Title VARCHAR(50) NOT NULL,
  Description VARCHAR(100) NOT NULL,
  Goal VARCHAR(100) NOT NULL,
  Weeks_Duration INT NOT NULL,
  Start_Date DATE NOT NULL,
  End_Date DATE NOT NULL,
  Status VARCHAR(50) NOT NULL,
  Created_at DATETIME NOT NULL,
  Updated_at DATETIME NOT NULL,
  Member_Id INT NOT NULL,
  Coach_ID INT NOT NULL,
  PRIMARY KEY (Workout_ID),
  FOREIGN KEY (Member_Id) REFERENCES MemberProfile(Member_Id),
  FOREIGN KEY (Coach_ID) REFERENCES CoachProfile(Coach_ID)
);

CREATE TABLE StaffAvailability
(
  Availability_ID INT NOT NULL,
  Is_Recurring INT NOT NULL,
  WeekDay INT NOT NULL,
  Available_Date DATE NOT NULL,
  Start_Time TIME NOT NULL,
  End_Time TIME NOT NULL,
  Max_Bookins_in_slot INT NOT NULL,
  Is_active INT NOT NULL,
  Created_at DATETIME NOT NULL,
  Updated_at DATETIME NOT NULL,
  Staff_User_ID INT NOT NULL,
  PRIMARY KEY (Availability_ID),
  FOREIGN KEY (Staff_User_ID) REFERENCES UserProfile(User_ID)
);

CREATE TABLE Appointment
(
  Appointment_ID INT NOT NULL,
  Appointment_Type VARCHAR(50) NOT NULL,
  Start_Date DATE NOT NULL,
  End_Date DATE NOT NULL,
  Status VARCHAR(50) NOT NULL,
  Notes_From_Member VARCHAR(300) NOT NULL,
  Location_Details VARCHAR(100) NOT NULL,
  Location_Type VARCHAR(100) NOT NULL,
  Notes_From_Staff_After_Session INT NOT NULL,
  Created_at DATETIME NOT NULL,
  Updated_at DATETIME NOT NULL,
  Staff_User_ID INT NOT NULL,
  Availability_ID INT NOT NULL,
  Member_Id INT NOT NULL,
  PRIMARY KEY (Appointment_ID),
  FOREIGN KEY (Staff_User_ID) REFERENCES UserProfile(User_ID),
  FOREIGN KEY (Availability_ID) REFERENCES StaffAvailability(Availability_ID),
  FOREIGN KEY (Member_Id) REFERENCES MemberProfile(Member_Id)
);

CREATE TABLE Exercise
(
  Exercise_ID INT NOT NULL,
  Name VARCHAR(50) NOT NULL,
  Description VARCHAR(100) NOT NULL,
  Difficultly VARCHAR(50) NOT NULL,
  Target_Muscle_Group VARCHAR(50) NOT NULL,
  Secondary_Muscles VARCHAR(50) NOT NULL,
  Instuctions VARCHAR(200) NOT NULL,
  Equipment_Required VARCHAR(200) NOT NULL,
  Video_url VARCHAR(200) NOT NULL,
  Created_at DATETIME NOT NULL,
  Updated_at DATETIME NOT NULL,
  PRIMARY KEY (Exercise_ID)
);

CREATE TABLE WorkoutExercise
(
  Workout_Exercise_ID INT NOT NULL,
  Sequence_Order INT NOT NULL,
  Day_Number VARCHAR(50) NOT NULL,
  Sets INT NOT NULL,
  Reps INT NOT NULL,
  Rest_Time VARCHAR(50) NOT NULL,
  Notes VARCHAR(100) NOT NULL,
  Exercise_ID INT NOT NULL,
  Workout_ID INT NOT NULL,
  PRIMARY KEY (Workout_Exercise_ID),
  FOREIGN KEY (Exercise_ID) REFERENCES Exercise(Exercise_ID),
  FOREIGN KEY (Workout_ID) REFERENCES WorkoutProgram(Workout_ID)
);

CREATE TABLE WorkoutLog
(
  Log_ID INT NOT NULL,
  Sets_Completed INT NOT NULL,
  Reps_per_set INT NOT NULL,
  Weight_per_set INT NOT NULL,
  Notes INT NOT NULL,
  Created_At DATETIME NOT NULL,
  Member_Id INT NOT NULL,
  Workout_ID INT NOT NULL,
  Workout_Exercise_ID INT NOT NULL,
  PRIMARY KEY (Log_ID),
  FOREIGN KEY (Member_Id) REFERENCES MemberProfile(Member_Id),
  FOREIGN KEY (Workout_ID) REFERENCES WorkoutProgram(Workout_ID),
  FOREIGN KEY (Workout_Exercise_ID) REFERENCES WorkoutExercise(Workout_Exercise_ID)
);

CREATE TABLE MealLog
(
  Meal_Log_ID INT NOT NULL,
  Log_date DATE NOT NULL,
  Notes VARCHAR(200) NOT NULL,
  Adherence_Percentage INT NOT NULL,
  Member_Id INT NOT NULL,
  Meal_Plan_ID INT NOT NULL,
  PRIMARY KEY (Meal_Log_ID),
  FOREIGN KEY (Member_Id) REFERENCES MemberProfile(Member_Id),
  FOREIGN KEY (Meal_Plan_ID) REFERENCES MealPlan(Meal_Plan_ID)
);

CREATE TABLE Meal
(
  Meal_ID INT NOT NULL AUTO_INCREMENT,
  Name VARCHAR(50) NOT NULL,
  Sequence_Order INT NOT NULL,
  Target_Time_of_Day INT NOT NULL,
  Created_at DATETIME NOT NULL,
  Updated_at DATETIME NOT NULL,
  Meal_Plan_ID INT NOT NULL,
  PRIMARY KEY (Meal_ID),
  FOREIGN KEY (Meal_Plan_ID) REFERENCES MealPlan(Meal_Plan_ID)
);

CREATE TABLE FoodItem
(
  Name VARCHAR(50) NOT NULL,
  Brand INT NOT NULL,
  Food_Item_ID INT NOT NULL AUTO_INCREMENT,
  Serving_Size INT NOT NULL,
  Calories INT NOT NULL,
  Sugar_Grams INT NOT NULL,
  Fats_Grams INT NOT NULL,
  Fiber_Grams INT NOT NULL,
  Protein_Grams INT NOT NULL,
  Carbs_Grams INT NOT NULL,
  Created_at DATETIME NOT NULL,
  Updated_at DATETIME NOT NULL,
  PRIMARY KEY (Food_Item_ID)
);

CREATE TABLE MealPlanItem
(
  Meal_Plan_Item_id INT NOT NULL AUTO_INCREMENT,
  Quantity_Servings INT NOT NULL,
  Notes VARCHAR(100) NOT NULL,
  Created_at DATETIME NOT NULL,
  Updated_at DATETIME NOT NULL,
  Meal_ID INT NOT NULL,
  Food_Item_ID INT NOT NULL,
  PRIMARY KEY (Meal_Plan_Item_id),
  FOREIGN KEY (Meal_ID) REFERENCES Meal(Meal_ID),
  FOREIGN KEY (Food_Item_ID) REFERENCES FoodItem(Food_Item_ID)
);

CREATE TABLE MembershipPlan
(
  Plan_ID INT NOT NULL,
  Name VARCHAR(50) NOT NULL,
  Tier VARCHAR(50) NOT NULL,
  Price INT NOT NULL,
  Duration INT NOT NULL,
  Coach_Access INT NOT NULL,
  Nutritionist_Access INT NOT NULL,
  Is_Active INT NOT NULL,
  Max_Nutritionist_Session INT NOT NULL,
  Max_Coach_Sessions INT NOT NULL,
  Max_Freeze_Length_days INT NOT NULL,
  Max_Freezes_Allowed INT NOT NULL,
  Created_at DATETIME NOT NULL,
  Updated_at DATETIME NOT NULL,
  PRIMARY KEY (Plan_ID)
);

CREATE TABLE MembershipSubscription
(
  Subscription_ID INT NOT NULL,
  Start_Date DATE NOT NULL,
  End_Date DATE NOT NULL,
  Status INT NOT NULL,
  Cancel_Date DATE NOT NULL,
  Cancel_Reason VARCHAR(100) NOT NULL,
  Is_Frozen INT NOT NULL,
  Total_Frozen_Days INT NOT NULL,
  Created_at DATETIME NOT NULL,
  Updated_at DATETIME NOT NULL,
  Member_Id INT NOT NULL,
  Plan_ID INT NOT NULL,
  Cancelled_by_User_ID INT NOT NULL,
  Cancelled_by_Admin_ID INT NOT NULL,
  PRIMARY KEY (Subscription_ID),
  FOREIGN KEY (Member_Id) REFERENCES MemberProfile(Member_Id),
  FOREIGN KEY (Plan_ID) REFERENCES MembershipPlan(Plan_ID),
  FOREIGN KEY (Cancelled_by_User_ID) REFERENCES UserProfile(User_ID),
  FOREIGN KEY (Cancelled_by_Admin_ID) REFERENCES UserProfile(User_ID)
);

CREATE TABLE MembershipFreeze
(
  Freeze_ID INT NOT NULL,
  Start_Date DATE NOT NULL,
  End_Date DATE NOT NULL,
  Actual_End_Date INT NOT NULL,
  Status INT NOT NULL,
  Reason VARCHAR(500) NOT NULL,
  Created_at DATETIME NOT NULL,
  Subscription_ID INT NOT NULL,
  Member_Id INT NOT NULL,
  PRIMARY KEY (Freeze_ID),
  FOREIGN KEY (Subscription_ID) REFERENCES MembershipSubscription(Subscription_ID),
  FOREIGN KEY (Member_Id) REFERENCES MemberProfile(Member_Id)
);

CREATE TABLE Conversation
(
  Conversation_ID INT NOT NULL,
  Conversation_Type VARCHAR(50) NOT NULL,
  Is_archived INT NOT NULL,
  Last_message_at DATE NOT NULL,
  unread_count_member INT NOT NULL,
  unread_count_staff INT NOT NULL,
  Member_Id INT NOT NULL,
  Staff_User_ID INT NOT NULL,
  PRIMARY KEY (Conversation_ID),
  FOREIGN KEY (Member_Id) REFERENCES MemberProfile(Member_Id),
  FOREIGN KEY (Staff_User_ID) REFERENCES UserProfile(User_ID)
);

CREATE TABLE Message
(
  Message_ID INT NOT NULL,
  Message_Text VARCHAR(500) NOT NULL,
  Attachment_Url INT NOT NULL,
  Attachment_Type INT NOT NULL,
  Sent_at INT NOT NULL,
  is_read INT NOT NULL,
  read_at INT NOT NULL,
  is_deleted INT NOT NULL,
  deleted_at INT NOT NULL,
  Conversation_ID INT NOT NULL,
  User_ID INT NOT NULL,
  PRIMARY KEY (Message_ID),
  FOREIGN KEY (Conversation_ID) REFERENCES Conversation(Conversation_ID),
  FOREIGN KEY (User_ID) REFERENCES UserProfile(User_ID)
);

CREATE TABLE Address
(
  Address_ID INT NOT NULL,
  Label VARCHAR(50) NOT NULL,
  Full_Name VARCHAR(100) NOT NULL,
  Phone_Number INT NOT NULL,
  Address_line1 VARCHAR(100) NOT NULL,
  Address_line2 VARCHAR(100) NOT NULL,
  City VARCHAR(50) NOT NULL,
  Governorate VARCHAR(50) NOT NULL,
  Postal_code INT NOT NULL,
  Is_Default_Shipping INT NOT NULL,
  Is_Default_Billing INT NOT NULL,
  Created_at DATETIME NOT NULL,
  Updated_at DATETIME NOT NULL,
  Member_Id INT NOT NULL,
  PRIMARY KEY (Address_ID),
  FOREIGN KEY (Member_Id) REFERENCES MemberProfile(Member_Id)
);

CREATE TABLE COrder
(
  Order_ID INT NOT NULL,
  Order_date DATE NOT NULL,
  Status VARCHAR(50) NOT NULL,
  Total_items INT NOT NULL,
  Subtotal_amount FLOAT NOT NULL,
  tax_amount FLOAT NOT NULL,
  shipping VARCHAR(100) NOT NULL,
  discount_amount FLOAT NOT NULL,
  total_amount FLOAT NOT NULL,
  currency INT NOT NULL,
  notes VARCHAR(100) NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  Member_Id INT NOT NULL,
  PRIMARY KEY (Order_ID),
  FOREIGN KEY (Member_Id) REFERENCES MemberProfile(Member_Id)
);

CREATE TABLE Cart
(
  Cart_ID INT NOT NULL,
  Status INT NOT NULL,
  Created_at DATETIME NOT NULL,
  Updated_at DATETIME NOT NULL,
  Member_Id INT NOT NULL,
  Order_ID INT NOT NULL,
  PRIMARY KEY (Cart_ID),
  FOREIGN KEY (Member_Id) REFERENCES MemberProfile(Member_Id),
  FOREIGN KEY (Order_ID) REFERENCES COrder(Order_ID)
);

CREATE TABLE ProductCategory
(
  Category_ID INT NOT NULL,
  Name VARCHAR(50) NOT NULL,
  Description VARCHAR(100) NOT NULL,
  Created_at DATETIME NOT NULL,
  Updated_at DATETIME NOT NULL,
  PRIMARY KEY (Category_ID)
);

CREATE TABLE Product
(
  Product_ID INT NOT NULL,
  Name VARCHAR(50) NOT NULL,
  Description VARCHAR(100) NOT NULL,
  Brand VARCHAR(50) NOT NULL,
  Sku INT NOT NULL,
  Price FLOAT NOT NULL,
  Cost_price FLOAT NOT NULL,
  Tax_rate FLOAT NOT NULL,
  is_active INT NOT NULL,
  thumbnail_url VARCHAR(200) NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  Category_ID INT NOT NULL,
  PRIMARY KEY (Product_ID),
  FOREIGN KEY (Category_ID) REFERENCES ProductCategory(Category_ID)
);

CREATE TABLE CartItem
(
  Cart_Item_ID INT NOT NULL,
  Quantity INT NOT NULL,
  Unit_price_at_add_time INT NOT NULL,
  Subtotal_amount INT NOT NULL,
  created_at DATETIME NOT NULL,
  Updated_at DATETIME NOT NULL,
  Cart_ID INT NOT NULL,
  Product_ID INT NOT NULL,
  PRIMARY KEY (Cart_Item_ID),
  FOREIGN KEY (Cart_ID) REFERENCES Cart(Cart_ID),
  FOREIGN KEY (Product_ID) REFERENCES Product(Product_ID)
);

CREATE TABLE ProductVariant
(
  Variant_ID INT NOT NULL,
  Variant_Name INT NOT NULL,
  Sku INT NOT NULL,
  Price_Override INT NOT NULL,
  Weight_Grams INT NOT NULL,
  Color INT NOT NULL,
  Size INT NOT NULL,
  Flavour INT NOT NULL,
  Created_at DATETIME NOT NULL,
  Updated_at DATETIME NOT NULL,
  Product_ID INT NOT NULL,
  PRIMARY KEY (Variant_ID),
  FOREIGN KEY (Product_ID) REFERENCES Product(Product_ID)
);

CREATE TABLE Inventory
(
  Inventory_ID INT NOT NULL,
  Quantity_in_stock INT NOT NULL,
  safety_stock_level INT NOT NULL,
  reorder_level INT NOT NULL,
  warehouse_location VARCHAR(50) NOT NULL,
  last_restocked_at DATE NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  Product_ID INT NOT NULL,
  PRIMARY KEY (Inventory_ID),
  FOREIGN KEY (Product_ID) REFERENCES Product(Product_ID)
);

CREATE TABLE OrderItem
(
  Order_Item_ID INT NOT NULL,
  Quantity INT NOT NULL,
  Unit_Price FLOAT NOT NULL,
  Discount_amount FLOAT NOT NULL,
  line_total_amount INT NOT NULL,
  created_at DATETIME NOT NULL,
  Order_ID INT NOT NULL,
  Product_ID INT NOT NULL,
  PRIMARY KEY (Order_Item_ID),
  FOREIGN KEY (Order_ID) REFERENCES COrder(Order_ID),
  FOREIGN KEY (Product_ID) REFERENCES Product(Product_ID)
);

CREATE TABLE Payment
(
  Payment_ID INT NOT NULL,
  Payment_provider VARCHAR(50) NOT NULL,
  transaction_reference VARCHAR(50) NOT NULL,
  Payment_date DATE NOT NULL,
  Amount FLOAT NOT NULL,
  Status VARCHAR(50) NOT NULL,
  Failure_reason VARCHAR(50) NOT NULL,
  Created_at DATETIME NOT NULL,
  Updated_at DATETIME NOT NULL,
  Order_ID INT NOT NULL,
  PRIMARY KEY (Payment_ID),
  FOREIGN KEY (Order_ID) REFERENCES COrder(Order_ID)
);

CREATE TABLE MedicalRecord
(
  Has_Diabetes INT NOT NULL,
  Has_Hypertension INT NOT NULL,
  Has_Heart_Condition INT NOT NULL,
  Has_Asthma INT NOT NULL,
  Has_Kidney_Disease INT NOT NULL,
  Has_Liver_Disease INT NOT NULL,
  Has_Thyroid_Disorder INT NOT NULL,
  Has_High_Cholesterol INT NOT NULL,
  Has_Back_Injury INT NOT NULL,
  Has_Neck_Injury INT NOT NULL,
  Has_Knee_injury INT NOT NULL,
  Has_shoulder_injury INT NOT NULL,
  Has_Elbow_injury INT NOT NULL,
  Has_hip_injury INT NOT NULL,
  Has_ankle_injury INT NOT NULL,
  Has_wrist_injury INT NOT NULL,
  Has_lactose_intolerance INT NOT NULL,
  Has_gluten_intolerance INT NOT NULL,
  Has_nut_Allergy INT NOT NULL,
  Has_shellfish_allergy INT NOT NULL,
  Has_egg_allergy INT NOT NULL,
  Is_pregnant INT NOT NULL,
  Is_smoker INT NOT NULL,
  has_recent_surgery INT NOT NULL,
  updated_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL,
  Member_Id INT NOT NULL,
  PRIMARY KEY (Member_Id),
  FOREIGN KEY (Member_Id) REFERENCES MemberProfile(Member_Id)
);