export default class RequestListController {

    constructor(requestsRepository) {
        this.requestsRepository = requestsRepository;
    }

    getRequests() {
        try {
            const requests = this.requestsRepository.getRequests();
            return requests;
        } catch (error) {
            console.log(error);
        }
    }
}