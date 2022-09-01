# Aspire (Lite Version)

Basically aspire lite version consists of following modules

- Customer
- Admin
- Loan
- Term

# Installation

Begin by pulling in the github repo

`git clone https://github.com/Sarav-S/aspire-code-challenge.git`

Next, install the composer packages

`composer install`

and then run the migration along with seeders

`php artisan migrate && php artisan db:seed`

Apart from above, no more additional configuration is required. Simply copy the contents of `.env.example`  to `.env` and modify the database connection string. Once that is done,  you're good to go.

# Basic Flow

Logical conditions

- All loan and term actions can be performed only by authenticated customer
- Customer can pay term amounts only for admin approved loans.

```mermaid
sequenceDiagram
Customer ->> Loan: Creates loan with amount and term (assuming 3)
Loan-->>Term: Loan terms is split based on user input
Admin->>Loan: Approves the Loan
Note right of Loan: Loan Approved Event fired
Customer ->> Loan: Fetches own loans approved by admin
Customer ->> Term: Pays for term 1
Customer ->> Term: Pays for term 2
Customer ->> Term: Pays for term 3
loop Term Settlement Check  
 Term->>Term: Check if all term is settled
end  
Note right of Term: Update Loan to PAID if all term is settled!
Term-->>Loan: All terms settled. Settle Loan.
Note left of Loan: Loan Closed Event fired

```

## Postman Collection

You can find the postman collection here : https://www.getpostman.com/collections/c2213e05c392cf7167ef


## Model Events

I've used Model events for handling terms creation and loan settlements. 

- When customer creates a loan, it'll be submitted for admin approval. Once admin approves the loan, terms for the loan will be created using the `update` model event and is processed by `LoanObserver`
- Each time customer makes term payment, term `update` event is fired and captured by `TermObserver` validating whether all terms have been settled. If settled, then respective loan's status will be changed to `PAID` 

## Jobs

I've created 3 jobs, 

- `LoanApproved` - Triggered when admin approves the loan. Can fire either SMS or Email.
- `LoanRejected` - Triggered when admin rejects the loan. Can fire either SMS or Email.
- `LoanClosed` - Triggered when customer pays all the term dues.

## Unhandled Cases

I've not handled the following scenarios for this lite version

- Recalculating the remaining term amount when customer pays extra term amount on a particular term
- Ability to pay all the loan amount in a single term 
- Date validation for the terms ie, as of now customer can pay all the terms on a single day
