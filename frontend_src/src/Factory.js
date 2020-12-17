import ExpectationListController from './controllers/ExpectationListController';
import ExpectationsRepository from './repositories/ExpectationsRepository';
import RequestListController from './controllers/RequestListController';
import RequestsRepository from './repositories/RequestsRepository';

export default class Factory {
    createRequestListController() {
        return new RequestListController(this.createRequestsRepository());
    }

    createRequestsRepository () {
        return new RequestsRepository();
    }

    createExpectationsListController() {
        return new ExpectationListController(this.createExpectationsRepository());
    }

    createExpectationsRepository () {
        return new ExpectationsRepository();
    }
}
