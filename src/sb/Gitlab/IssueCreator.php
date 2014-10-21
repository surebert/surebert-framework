<?php
/**
 * Used to open issues in gitlab
 * requires "m4tthumphrey/php-gitlab-api"
 */
namespace sb\Gitlab;

class IssueCreator {

    /**
     * The client connectionto gitlab using private key
     * @var string 
     */
    public $client;

    /**
     * The gitlab host to connec to
     * @var string 
     */
    public $gitlab_host;

    /**
     * Connects to gitlab
     * @param string $gitlab_host The gitlab host to connect to
     * @param string $private_key The private key
     * <code>
     * $gitlab = new \sb\Gitlab\IssueCreator('https://gitlab.yoursite.com','YOUR_PRIVATE_TOKEN');
     * $issue = $gitlab->addIssue('namespace:project', 'someone@email.com', 'This does not work..','This doesnt work properly. Please fix');
     * </code>
     */
    public function __construct($gitlab_host, $private_key) {
        $this->gitlab_host = $gitlab_host;
        $this->client = new \Gitlab\Client($this->gitlab_host . '/api/v3/');
        $this->client->authenticate($private_key, \Gitlab\Client::AUTH_URL_TOKEN);
    }

    /**
     * Adds an issue to a git repository
     * @param string $project_name 
     * @param string $assignee_email
     * @param string $title
     * @param string $description
     * @return \Gitlab\Model\Issue
     * @throws \Exception
     */
    public function addIssue($project_name, $assignee_email, $title, $description) {

        if (!strstr($project_name, ":")) {
            throw (new \Exception("You must search for project with namespace: prefix"));
        }
        $parts = explode(':', $project_name);

        $api_project = $this->client->api('projects');
        $projects = $api_project->search($parts[1]);

        $found_project = false;
        foreach ($projects as $project) {
            if (preg_match("~^" . $parts[0] . "$~", $project['namespace']['name'])) {
                $found_project = $project;
                break;
            }
        }

        if (!$found_project) {
            throw (new \Exception("Project not found"));
        }

        $api_user = $this->client->api('users');
        $users = $api_user->search($assignee_email, true);
        if (count($users) != 1) {
            throw (new \Exception("More than one user matches assignee"));
        }
        $user = $users[0];

        $project = new \Gitlab\Model\Project($projects[0]['id'], $this->client);
        $issue = $project->createIssue($title, array(
            'description' => $description,
            'assignee_id' => $user['id']
        ));

        return $issue;
    }

}
